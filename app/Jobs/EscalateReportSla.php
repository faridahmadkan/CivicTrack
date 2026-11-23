<?php

namespace App\Jobs;

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Enums\WorkforceApplicationType;
use App\Models\User;
use App\Models\UnitMembership;
use App\Notifications\SlaEscalationNotification;
use App\Services\ReportSlaService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class EscalateReportSla implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, \Illuminate\Bus\Queueable, SerializesModels;

    public int $uniqueFor = 600;

    public function __construct(public readonly int $reportSlaInstanceId) {}

    public function uniqueId(): string
    {
        return 'report-sla:'.$this->reportSlaInstanceId;
    }

    public function handle(ReportSlaService $service): void
    {
        $result = $service->processDueEscalation($this->reportSlaInstanceId);

        if ($result === null) {
            return;
        }

        $staffIds = $result['report']->routed_unit_id === null
            ? collect()
            : UnitMembership::query()
                ->where('organization_unit_id', $result['report']->routed_unit_id)
                ->where('membership_type', WorkforceApplicationType::Staff->value)
                ->where('status', 'active')
                ->pluck('user_id');

        $recipients = User::query()
            ->where('account_status', AccountStatus::Active->value)
            ->where(function ($query) use ($staffIds): void {
                $query->whereHas('roles', fn ($roles) => $roles->where('name', RoleName::Administrator->value));

                if ($staffIds->isNotEmpty()) {
                    $query->orWhereIn('id', $staffIds);
                }
            })
            ->get();

        if ($recipients->isEmpty()) {
            Log::warning('CivicTrack raised an SLA escalation without an active administrator or unit staff recipient.', [
                'report_reference' => $result['report']->public_reference,
                'report_sla_instance_id' => $result['instance']->id,
                'escalation_count' => $result['instance']->escalation_count,
            ]);

            return;
        }

        Notification::send($recipients, new SlaEscalationNotification(
            $result['report'],
            $result['instance'],
            $result['targets'],
        ));
    }
}
