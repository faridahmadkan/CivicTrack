<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WorkforceApplicationStatus;
use App\Enums\WorkforceApplicationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewWorkforceApplicationRequest;
use App\Models\WorkforceApplication;
use App\Models\WorkforceApplicationDocument;
use App\Services\WorkforceApplicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkforceApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', WorkforceApplication::class);

        $availableTypes = array_values(array_filter(
            WorkforceApplicationType::cases(),
            fn (WorkforceApplicationType $type): bool => $request->user()->can($type->reviewPermission()->value),
        ));

        $selectedType = $request->query('type');
        $query = WorkforceApplication::query()
            ->with(['user', 'requestedUnit'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        if ($selectedType !== null) {
            $type = WorkforceApplicationType::tryFrom((string) $selectedType);
            abort_unless($type !== null && in_array($type, $availableTypes, true), 404);
            $query->where('application_type', $type->value);
        } else {
            $query->whereIn('application_type', array_map(
                static fn (WorkforceApplicationType $type): string => $type->value,
                $availableTypes,
            ));
        }

        return view('admin.workforce-applications.index', [
            'applications' => $query->paginate(20)->withQueryString(),
            'selectedType' => $selectedType,
            'availableTypes' => $availableTypes,
        ]);
    }

    public function show(WorkforceApplication $workforceApplication): View
    {
        $this->authorize('view', $workforceApplication);

        $workforceApplication->load(['user', 'requestedUnit', 'reviewer', 'documents', 'events']);

        return view('admin.workforce-applications.show', ['application' => $workforceApplication]);
    }

    public function review(
        ReviewWorkforceApplicationRequest $request,
        WorkforceApplication $workforceApplication,
        WorkforceApplicationService $service,
    ): RedirectResponse {
        $status = WorkforceApplicationStatus::from($request->validated('status'));
        $service->review($workforceApplication, $request->user(), $status, $request->validated('reason'));

        return to_route('admin.workforce-applications.show', $workforceApplication)
            ->with('status', 'Application status updated. The applicant will be notified.');
    }

    public function downloadDocument(WorkforceApplicationDocument $document): StreamedResponse
    {
        $this->authorize('review', $document->application);

        abort_unless(Storage::disk($document->storage_disk)->exists($document->storage_path), 404);

        $response = Storage::disk($document->storage_disk)->download(
            $document->storage_path,
            $document->original_name,
            ['Content-Type' => $document->mime_type, 'X-Content-Type-Options' => 'nosniff'],
        );

        return $response;
    }
}
