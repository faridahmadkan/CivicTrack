<?php

namespace App\Notifications;

use App\Models\CivicReport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\AnonymousNotifiable;

class CivicReportStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly CivicReport $report) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reportUrl = $notifiable instanceof User
            ? route('citizen.reports.show', $this->report)
            : route('public.reports.access', $this->report);

        return (new MailMessage)
            ->subject('CivicTrack report status updated')
            ->greeting($notifiable instanceof AnonymousNotifiable ? 'Hello,' : 'Hello '.$notifiable->name.',')
            ->line('Report '.$this->report->public_reference.' is now '.$this->report->status->label().'.')
            ->action('Open report tracking', $reportUrl)
            ->when(! ($notifiable instanceof User), function (MailMessage $message): void {
                $message->line('Use the private access code you saved when you submitted the report.');
            })
            ->salutation('CivicTrack');
    }

    /** @return array<string, int|string> */
    public function toArray(object $notifiable): array
    {
        $reportUrl = $notifiable instanceof User
            ? route('citizen.reports.show', $this->report)
            : route('public.reports.access', $this->report);

        return [
            'report_id' => $this->report->id,
            'public_reference' => $this->report->public_reference,
            'status' => $this->report->status->value,
            'status_label' => $this->report->status->label(),
            'url' => $reportUrl,
        ];
    }
}
