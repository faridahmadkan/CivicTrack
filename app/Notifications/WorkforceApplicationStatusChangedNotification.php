<?php

namespace App\Notifications;

use App\Models\WorkforceApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkforceApplicationStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly WorkforceApplication $application) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update on your CivicTrack workforce application')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('The status of application '.$this->application->application_number.' is now '.$this->application->status->label().'.')
            ->when($this->application->decision_reason !== null, function (MailMessage $message): void {
                $message->line('Decision details: '.$this->application->decision_reason);
            })
            ->when($this->application->requested_information !== null, function (MailMessage $message): void {
                $message->line('Additional information requested: '.$this->application->requested_information);
            })
            ->action('View application', route('workforce.applications.show', $this->application))
            ->salutation('CivicTrack');
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'application_number' => $this->application->application_number,
            'application_type' => $this->application->application_type->value,
            'status' => $this->application->status->value,
            'status_label' => $this->application->status->label(),
            'url' => route('workforce.applications.show', $this->application),
        ];
    }
}
