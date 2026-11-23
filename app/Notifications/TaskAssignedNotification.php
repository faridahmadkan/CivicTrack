<?php

namespace App\Notifications;

use App\Models\TaskAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly TaskAssignment $assignment) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $task = $this->assignment->task;

        return (new MailMessage)
            ->subject('A CivicTrack field task was assigned to you')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Task '.$task->task_number.' has been assigned to you.')
            ->line('Work order: '.$task->workOrder->reference.' · '.$task->title)
            ->when($this->assignment->assignment_instructions !== null, function (MailMessage $message): void {
                $message->line('Instructions: '.$this->assignment->assignment_instructions);
            })
            ->action('Open field task', route('worker.tasks.show', $task))
            ->salutation('CivicTrack');
    }

    /** @return array<string, int|string> */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->assignment->work_task_id,
            'task_number' => $this->assignment->task->task_number,
            'assignment_id' => $this->assignment->id,
            'status' => $this->assignment->status->value,
            'url' => route('worker.tasks.show', $this->assignment->task),
        ];
    }
}
