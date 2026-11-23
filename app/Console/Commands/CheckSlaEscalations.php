<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Enums\ReportSlaState;
use App\Jobs\EscalateReportSla;
use App\Models\ReportSlaInstance;

#[Signature('civictrack:sla:check')]
#[Description('Queue escalations for civic reports that have missed their service targets.')]
class CheckSlaEscalations extends Command
{
    public function handle(): int
    {
        $queued = 0;

        ReportSlaInstance::query()
            ->whereIn('state', [ReportSlaState::Active->value, ReportSlaState::Breached->value])
            ->whereNull('resolved_at')
            ->where(function ($query): void {
                $query->where(function ($targets): void {
                    $targets->whereNull('first_response_at')
                        ->whereNull('first_response_breached_at')
                        ->where('first_response_due_at', '<', now());
                })->orWhere(function ($targets): void {
                    $targets->whereNull('resolution_breached_at')
                        ->where('resolution_due_at', '<', now());
                })->orWhere(function ($targets): void {
                    $targets->whereNotNull('next_escalation_at')
                        ->where('next_escalation_at', '<', now());
                });
            })
            ->orderBy('id')
            ->chunkById(100, function ($instances) use (&$queued): void {
                foreach ($instances as $instance) {
                    EscalateReportSla::dispatch($instance->id);
                    $queued++;
                }
            });

        $this->info("Queued {$queued} SLA escalation check job(s).");

        return self::SUCCESS;
    }
}
