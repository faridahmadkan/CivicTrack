<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyTaskAssigned implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(TaskAssigned $event): void
    {
        $event->assignment->worker->notify(new TaskAssignedNotification($event->assignment));
    }
}
