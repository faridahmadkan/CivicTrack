<?php

namespace App\Http\Controllers\Workforce;

use App\Enums\RoleName;
use App\Enums\WorkforceApplicationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProvideWorkforceInformationRequest;
use App\Http\Requests\StoreWorkforceApplicationRequest;
use App\Models\OrganizationUnit;
use App\Models\WorkforceApplication;
use App\Models\WorkforceApplicationDocument;
use App\Services\RegistrationControlService;
use App\Services\WorkforceApplicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkforceApplicationController extends Controller
{
    public function index(Request $request, RegistrationControlService $registrationControls): View
    {
        return view('workforce.applications.index', [
            'applications' => WorkforceApplication::query()
                ->whereBelongsTo($request->user())
                ->with('requestedUnit')
                ->orderByDesc('submitted_at')
                ->orderByDesc('id')
                ->get(),
            'canApply' => $request->user()->hasRole(RoleName::Citizen->value)
                && ! $request->user()->hasAnyRole([RoleName::Staff->value, RoleName::Worker->value]),
            'staffApplicationsOpen' => $registrationControls->isOpen(RoleName::Staff),
            'workerApplicationsOpen' => $registrationControls->isOpen(RoleName::Worker),
        ]);
    }

    public function create(string $type, RegistrationControlService $registrationControls): View
    {
        $applicationType = WorkforceApplicationType::tryFrom($type);
        abort_unless($applicationType !== null, 404);
        $this->authorize('create', WorkforceApplication::class);

        return view('workforce.applications.create', [
            'type' => $applicationType,
            'registrationOpen' => $registrationControls->isOpen($applicationType->registrationRole()),
            'organizationUnits' => OrganizationUnit::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
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

    public function downloadDocument(Request $request, WorkforceApplicationDocument $document): StreamedResponse
    {
        abort_unless($document->application->isOwnedBy($request->user()), 404);
        $this->authorize('view', $document->application);

        abort_unless(Storage::disk($document->storage_disk)->exists($document->storage_path), 404);

        return Storage::disk($document->storage_disk)->download(
            $document->storage_path,
            $document->original_name,
            ['Content-Type' => $document->mime_type, 'X-Content-Type-Options' => 'nosniff'],
        );
    }
}
