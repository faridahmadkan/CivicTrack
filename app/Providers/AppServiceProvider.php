<?php

namespace App\Providers;

use App\Events\WorkforceApplicationReviewed;
use App\Events\TaskAssigned;
use App\Events\CivicReportStatusChanged;
use App\Listeners\NotifyTaskAssigned;
use App\Listeners\NotifyCivicReportStatusChanged;
use App\Listeners\NotifyWorkforceApplicant;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(WorkforceApplicationReviewed::class, NotifyWorkforceApplicant::class);
        Event::listen(TaskAssigned::class, NotifyTaskAssigned::class);
        Event::listen(CivicReportStatusChanged::class, NotifyCivicReportStatusChanged::class);
    }
}
