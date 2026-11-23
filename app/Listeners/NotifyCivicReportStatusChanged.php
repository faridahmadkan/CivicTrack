<?php

namespace App\Listeners;

use App\Events\CivicReportStatusChanged;
use App\Notifications\CivicReportStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifyCivicReportStatusChanged implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CivicReportStatusChanged $event): void
    {
        $report = $event->report->fresh(['reporter']);
        $notification = new CivicReportStatusChangedNotification($report);

        if ($report->reporter !== null) {
            $report->reporter->notify($notification);

            return;
        }

        if ($report->guest_contact_email !== null) {
            Notification::route('mail', $report->guest_contact_email)->notify($notification);
        }
    }
}
