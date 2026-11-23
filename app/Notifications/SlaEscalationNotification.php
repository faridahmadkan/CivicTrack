<?php

namespace App\Notifications;

use App\Models\CivicReport;
use App\Models\ReportSlaInstance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaEscalationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param array<int, string> $targets */
    public function __construct(
        private readonly CivicReport $report,
        private readonly ReportSlaInstance $instance,
        private readonly array $targets,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $targetLabels = collect($this->targets)
            ->map(fn (string $target): string => $target === 'first_response' ? 'first response' : 'resolution')
            ->implode(' and ');

        return (new MailMessage)
            ->subject('CivicTrack service target escalation')
            ->greeting('Civic service target escalation')
            ->line('Report '.$this->report->public_reference.' has an overdue '.$targetLabels.' service target.')
            ->line('Policy: '.$this->instance->policy_name_snapshot)
            ->line('Escalation number: '.$this->instance->escalation_count)
            ->action('Review report', route('staff.reports.show', $this->report))
            ->salutation('CivicTrack Accountability');
    }

    /** @return array<string, int|string|array<int, string>> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'report_sla_escalation',
            'report_reference' => $this->report->public_reference,
            'report_sla_instance_id' => $this->instance->id,
            'policy_name' => $this->instance->policy_name_snapshot,
            'targets' => $this->targets,
            'escalation_count' => $this->instance->escalation_count,
            'url' => route('staff.reports.show', $this->report),
        ];
    }
}
