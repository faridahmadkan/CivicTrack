<?php

namespace App\Listeners;

use App\Events\WorkforceApplicationReviewed;
use App\Notifications\WorkforceApplicationStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyWorkforceApplicant implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(WorkforceApplicationReviewed $event): void
    {
        $event->application->user->notify(
            new WorkforceApplicationStatusChangedNotification($event->application),
        );
    }
}
