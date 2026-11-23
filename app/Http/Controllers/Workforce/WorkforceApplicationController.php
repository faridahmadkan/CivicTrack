<?php

namespace App\Http\Controllers\Workforce;

use App\Enums\RoleName;
use App\Enums\WorkforceApplicationStatus;
use App\Enums\WorkforceApplicationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProvideWorkforceInformationRequest;
use App\Http\Requests\StoreWorkforceApplicantRequest;
use App\Http\Requests\StoreWorkforceApplicationRequest;
use App\Models\OrganizationUnit;
use App\Models\WorkforceApplication;
use App\Services\RegistrationControlService;
use App\Services\WorkforceApplicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Throwable;

class WorkforceApplicationController extends Controller
{
    public function index(Request $request, RegistrationControlService $registrationControls): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::enum(WorkforceApplicationType::class)],
            'status' => ['nullable', Rule::enum(WorkforceApplicationStatus::class)],
        ]);
        $query = WorkforceApplication::query()
            ->whereBelongsTo($request->user())
            ->with('requestedUnit');

        if (filled($filters['q'] ?? null)) {
            $search = '%'.trim($filters['q']).'%';
            $query->where(static function (Builder $applications) use ($search): void {
                $applications->where('application_number', 'like', $search)
                    ->orWhere('position_title', 'like', $search)
                    ->orWhereHas('requestedUnit', static fn (Builder $units) => $units->where('name', 'like', $search));
            });
        }

        if (filled($filters['type'] ?? null)) {
            $query->where('application_type', $filters['type']);
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        return view('workforce.applications.index', [
            'applications' => $query
                ->orderByDesc('submitted_at')
                ->orderByDesc('id')
                ->paginate(10)
                ->withQueryString(),
            'filters' => $filters,
            'canApply' => $request->user()->hasRole(RoleName::Citizen->value)
                && ! $request->user()->hasAnyRole([RoleName::Staff->value, RoleName::Worker->value]),
            'staffApplicationsOpen' => $registrationControls->isOpen(RoleName::Staff),
            'workerApplicationsOpen' => $registrationControls->isOpen(RoleName::Worker),
        ]);
    }

    public function create(Request $request, string $type, RegistrationControlService $registrationControls): View
    {
        $applicationType = WorkforceApplicationType::tryFrom($type);
        abort_unless($applicationType !== null, 404);

        if ($request->user() !== null) {
            $this->authorize('create', WorkforceApplication::class);
        }

        return view('workforce.applications.create', [
            'type' => $applicationType,
            'registrationOpen' => $registrationControls->isOpen($applicationType->registrationRole()),
            'isGuestApplicant' => $request->user() === null,
            'requireEmailVerification' => config('civictrack.require_email_verification'),
            'mapCenter' => [config('civictrack.map.latitude'), config('civictrack.map.longitude')],
            'organizationUnits' => OrganizationUnit::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function register(
        StoreWorkforceApplicantRequest $request,
        string $type,
        WorkforceApplicationService $service,
    ): RedirectResponse {
        $applicationType = WorkforceApplicationType::tryFrom($type);
        abort_unless($applicationType !== null, 404);

        $attributes = $request->validated();
        $documents = $attributes['documents'] ?? [];
        unset($attributes['documents']);
        $registration = $service->registerApplicant($applicationType, $attributes, $documents);
        $user = $registration['user'];

        Auth::login($user);
        $request->session()->regenerate();

        if (config('civictrack.require_email_verification')) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return to_route('home')->with('status', 'Your workforce application is pending administrator review. You can sign in and follow its status from your citizen dashboard.');
    }

    public function store(
        StoreWorkforceApplicationRequest $request,
        string $type,
        WorkforceApplicationService $service,
    ): RedirectResponse {
        $applicationType = WorkforceApplicationType::tryFrom($type);
        abort_unless($applicationType !== null, 404);
        $attributes = $request->validated();
        $documents = $attributes['documents'] ?? [];
        unset($attributes['documents']);

        $application = $service->submit($request->user(), $applicationType, $attributes, $documents);

        return to_route('workforce.applications.show', $application)
            ->with('status', 'Your workforce application was submitted. Its reference is '.$application->application_number.'.');
    }

    public function show(Request $request, WorkforceApplication $workforceApplication): View
    {
        abort_unless($workforceApplication->isOwnedBy($request->user()), 404);
        $this->authorize('view', $workforceApplication);

        $workforceApplication->load(['requestedUnit', 'documents', 'events']);

        return view('workforce.applications.show', ['application' => $workforceApplication]);
    }

    public function provideInformation(
        ProvideWorkforceInformationRequest $request,
        WorkforceApplication $workforceApplication,
        WorkforceApplicationService $service,
    ): RedirectResponse {
        $attributes = $request->validated();
        $documents = $attributes['documents'] ?? [];
        unset($attributes['documents']);

        $service->provideRequestedInformation($workforceApplication, $request->user(), $attributes, $documents);

        return to_route('workforce.applications.show', $workforceApplication)
            ->with('status', 'Your additional information has been sent for review.');
    }
}
