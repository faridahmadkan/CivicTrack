<?php

use App\Http\Controllers\Account\AccountHomeController;
use App\Http\Controllers\Account\NotificationCenterController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AdminHomeController;
use App\Http\Controllers\Admin\AuditEventController;
use App\Http\Controllers\Admin\OrganizationUnitController;
use App\Http\Controllers\Admin\RegistrationControlController;
use App\Http\Controllers\Admin\ReportSlaPolicyController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\WorkforceApplicationController as AdminWorkforceApplicationController;
use App\Http\Controllers\Citizen\CivicReportController as CitizenCivicReportController;
use App\Http\Controllers\Citizen\DashboardController as CitizenDashboardController;
use App\Http\Controllers\PrivateFileController;
use App\Http\Controllers\Public\CommunityMapController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PlaceSearchController;
use App\Http\Controllers\Public\ReportFeedbackController;
use App\Http\Controllers\Public\ReportSubmissionController;
use App\Http\Controllers\Staff\CivicReportController as StaffCivicReportController;
use App\Http\Controllers\Staff\ReportReopenController;
use App\Http\Controllers\Staff\TaskManagementController;
use App\Http\Controllers\Staff\WorkerRatingController;
use App\Http\Controllers\Staff\WorkOrderController;
use App\Http\Controllers\Worker\TaskController as WorkerTaskController;
use App\Http\Controllers\Workforce\WorkforceApplicationController;
use App\Models\WorkforceApplication;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('public.home');
Route::view('/privacy', 'public.privacy')->name('privacy.notice');
Route::get('/map', CommunityMapController::class)->name('public.map.index');
Route::post('/places/search', PlaceSearchController::class)->middleware('throttle:10,1')->name('public.places.search');

Route::get('/reports/create', [ReportSubmissionController::class, 'create'])->name('public.reports.create');
Route::post('/reports', [ReportSubmissionController::class, 'store'])->middleware('throttle:6,1')->name('public.reports.store');
Route::get('/reports/{report}/submitted', [ReportSubmissionController::class, 'submitted'])
    ->middleware('cache.headers:no_store;private')->name('public.reports.submitted');
Route::get('/reports/{report}/access', [ReportSubmissionController::class, 'access'])
    ->middleware('cache.headers:no_store;private')->name('public.reports.access');
Route::post('/reports/{report}/access', [ReportSubmissionController::class, 'verifyAccess'])
    ->middleware(['throttle:8,1', 'cache.headers:no_store;private'])->name('public.reports.verify-access');
Route::get('/reports/{report}/track', [ReportSubmissionController::class, 'show'])
    ->middleware('cache.headers:no_store;private')->name('public.reports.track.show');
Route::post('/reports/{report}/comments', [ReportSubmissionController::class, 'comment'])
    ->middleware(['throttle:8,1', 'cache.headers:no_store;private'])->name('public.reports.track.comments.store');
Route::post('/reports/{report}/feedback', [ReportFeedbackController::class, 'store'])
    ->middleware(['throttle:4,1', 'cache.headers:no_store;private'])->name('public.reports.feedback.store');
Route::post('/reports/{report}/reopen-requests', [ReportFeedbackController::class, 'requestReopen'])
    ->middleware(['throttle:4,1', 'cache.headers:no_store;private'])->name('public.reports.reopen-requests.store');
Route::post('/reports/{report}/cancel', [ReportFeedbackController::class, 'cancel'])
    ->middleware(['throttle:4,1', 'cache.headers:no_store;private'])->name('public.reports.cancel');
Route::post('/reports/{report}/disputes', [ReportFeedbackController::class, 'dispute'])
    ->middleware(['throttle:4,1', 'cache.headers:no_store;private'])->name('public.reports.disputes.store');
Route::get('/reports/{report}/attachments/{attachment}', [PrivateFileController::class, 'reportAttachment'])
    ->middleware('cache.headers:no_store;private')->name('public.reports.track.attachments.download');

Route::middleware(['auth', 'verified', 'account.active', 'cache.headers:no_store;private', 'can:reports.view.own'])
    ->get('/citizen', CitizenDashboardController::class)
    ->name('citizen.home');

Route::middleware(['auth', 'verified', 'account.active', 'cache.headers:no_store;private'])
    ->get('/home', AccountHomeController::class)
    ->name('home');

Route::middleware(['auth', 'verified', 'account.active', 'cache.headers:no_store;private'])->prefix('account/notifications')->name('account.notifications.')->group(function (): void {
    Route::get('/', [NotificationCenterController::class, 'index'])->name('index');
    Route::patch('/{notification}/read', [NotificationCenterController::class, 'markRead'])->name('read');
    Route::post('/read-all', [NotificationCenterController::class, 'markAllRead'])->name('read-all');
});

Route::middleware(['auth', 'verified', 'account.active', 'cache.headers:no_store;private'])->prefix('workforce')->name('workforce.')->group(function (): void {
    Route::get('/applications', [WorkforceApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{type}/create', [WorkforceApplicationController::class, 'create'])->name('applications.create');
    Route::post('/applications/{type}', [WorkforceApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{workforceApplication}', [WorkforceApplicationController::class, 'show'])
        ->name('applications.show');
    Route::put('/applications/{workforceApplication}/information', [WorkforceApplicationController::class, 'provideInformation'])
        ->name('applications.information');
    Route::get('/application-documents/{document}', [PrivateFileController::class, 'workforceDocument'])
        ->name('application-documents.download');
});

Route::middleware(['auth', 'verified', 'account.active', 'cache.headers:no_store;private', 'can:reports.create'])
    ->prefix('citizen/reports')
    ->name('citizen.reports.')
    ->group(function (): void {
        Route::get('/', [CitizenCivicReportController::class, 'index'])->name('index');
        Route::get('/create', [CitizenCivicReportController::class, 'create'])->name('create');
        Route::post('/', [CitizenCivicReportController::class, 'store'])->name('store');
        Route::get('/{report}', [CitizenCivicReportController::class, 'show'])->name('show');
        Route::post('/{report}/comments', [CitizenCivicReportController::class, 'comment'])->name('comments.store');
        Route::get('/{report}/attachments/{attachment}', [PrivateFileController::class, 'reportAttachment'])
            ->name('attachments.download');
    });

Route::middleware(['auth', 'verified', 'account.active', 'cache.headers:no_store;private'])
    ->prefix('staff/reports')
    ->name('staff.reports.')
    ->group(function (): void {
        Route::get('/', [StaffCivicReportController::class, 'index'])->name('index');
        Route::get('/{civicReport}', [StaffCivicReportController::class, 'show'])->name('show');
        Route::post('/{civicReport}/related/{relatedReport}', [StaffCivicReportController::class, 'linkRelated'])->name('related.store');
        Route::post('/{civicReport}/duplicates/{duplicateReport}', [StaffCivicReportController::class, 'markDuplicate'])->name('duplicates.store');
        Route::post('/{civicReport}/duplicates/{duplicateReport}/merge', [StaffCivicReportController::class, 'mergeDuplicate'])->name('duplicates.merge');
        Route::put('/{civicReport}/dispute-review', [StaffCivicReportController::class, 'reviewDispute'])->name('disputes.review');
        Route::put('/{civicReport}/triage', [StaffCivicReportController::class, 'triage'])->name('triage');
        Route::post('/{civicReport}/comments', [StaffCivicReportController::class, 'comment'])->name('comments.store');
        Route::get('/{civicReport}/attachments/{attachment}', [PrivateFileController::class, 'staffReportAttachment'])
            ->name('attachments.download');
        Route::post('/{report}/work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
    });

Route::middleware(['auth', 'verified', 'account.active', 'cache.headers:no_store;private'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function (): void {
        Route::get('/reports/{report}/work-orders/create', [WorkOrderController::class, 'create'])->name('work-orders.create');
        Route::get('/work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('work-orders.show');
        Route::post('/tasks/{workTask}/assignments', [TaskManagementController::class, 'assign'])->name('tasks.assign');
        Route::put('/tasks/{workTask}/verification', [TaskManagementController::class, 'verify'])->name('tasks.verify');
        Route::post('/tasks/{workTask}/assignments/{taskAssignment}/rating', [WorkerRatingController::class, 'store'])
            ->middleware('can:feedback.rate_workers')->name('tasks.ratings.store');
        Route::put('/reopen-requests/{reopenRequest}', [ReportReopenController::class, 'review'])
            ->middleware('can:reports.view.department')->name('reopen-requests.review');
        Route::get('/tasks/{workTask}/evidence/{evidence}', [PrivateFileController::class, 'staffTaskEvidence'])
            ->name('tasks.evidence.download');
    });

Route::middleware(['auth', 'verified', 'account.active', 'cache.headers:no_store;private', 'can:assignments.view.own'])
    ->prefix('worker')
    ->name('worker.')
    ->group(function (): void {
        Route::get('/', [WorkerTaskController::class, 'home'])->name('home');
        Route::get('/tasks', [WorkerTaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{workTask}', [WorkerTaskController::class, 'show'])->name('tasks.show');
        Route::post('/tasks/{workTask}/accept', [WorkerTaskController::class, 'accept'])->name('tasks.accept');
        Route::post('/tasks/{workTask}/decline', [WorkerTaskController::class, 'decline'])->name('tasks.decline');
        Route::post('/tasks/{workTask}/start', [WorkerTaskController::class, 'start'])->name('tasks.start');
        Route::post('/tasks/{workTask}/updates', [WorkerTaskController::class, 'update'])->name('tasks.updates.store');
        Route::post('/tasks/{workTask}/evidence', [WorkerTaskController::class, 'storeEvidence'])->name('tasks.evidence.store');
        Route::post('/tasks/{workTask}/submit', [WorkerTaskController::class, 'submit'])->name('tasks.submit');
        Route::get('/tasks/{workTask}/evidence/{evidence}', [PrivateFileController::class, 'taskEvidence'])
            ->name('tasks.evidence.download');
    });

Route::middleware(['auth', 'verified', 'account.active', 'cache.headers:no_store;private', 'can:platform.access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', AdminHomeController::class)->name('home');

        Route::middleware('can:accounts.manage')->group(function (): void {
            Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
            Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
        });

        Route::middleware('can:organization.manage')->group(function (): void {
            Route::get('/organization-units', [OrganizationUnitController::class, 'index'])->name('organization-units.index');
            Route::post('/organization-units', [OrganizationUnitController::class, 'store'])->name('organization-units.store');
            Route::put('/organization-units/{organizationUnit}', [OrganizationUnitController::class, 'update'])
                ->can('update', 'organizationUnit')
                ->name('organization-units.update');
        });

        Route::middleware('can:registrations.manage')->group(function (): void {
            Route::get('/registration-controls', [RegistrationControlController::class, 'index'])->name('registration-controls.index');
            Route::put('/registration-controls/{context}', [RegistrationControlController::class, 'update'])->name('registration-controls.update');
        });

        Route::middleware('can:catalog.manage')->group(function (): void {
            Route::get('/service-categories', [ServiceCategoryController::class, 'index'])->name('service-categories.index');
            Route::post('/service-categories', [ServiceCategoryController::class, 'store'])->name('service-categories.store');
            Route::put('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'update'])
                ->name('service-categories.update');
        });

        Route::middleware('can:sla.manage')->group(function (): void {
            Route::get('/sla-policies', [ReportSlaPolicyController::class, 'index'])->name('sla-policies.index');
            Route::post('/sla-policies', [ReportSlaPolicyController::class, 'store'])->name('sla-policies.store');
            Route::put('/sla-policies/{reportSlaPolicy}', [ReportSlaPolicyController::class, 'update'])->name('sla-policies.update');
        });

        Route::middleware('can:audit.view')->get('/audit-events', [AuditEventController::class, 'index'])
            ->name('audit-events.index');

        Route::get('/workforce-applications', [AdminWorkforceApplicationController::class, 'index'])
            ->can('viewAny', WorkforceApplication::class)
            ->name('workforce-applications.index');
        Route::get('/workforce-applications/{workforceApplication}', [AdminWorkforceApplicationController::class, 'show'])
            ->can('view', 'workforceApplication')
            ->name('workforce-applications.show');
        Route::put('/workforce-applications/{workforceApplication}', [AdminWorkforceApplicationController::class, 'review'])
            ->can('review', 'workforceApplication')
            ->name('workforce-applications.review');
        Route::get('/workforce-application-documents/{document}', [PrivateFileController::class, 'workforceDocument'])
            ->name('workforce-application-documents.download');
    });
