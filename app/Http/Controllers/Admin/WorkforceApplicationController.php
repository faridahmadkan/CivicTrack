<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WorkforceApplicationStatus;
use App\Enums\WorkforceApplicationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewWorkforceApplicationRequest;
use App\Models\WorkforceApplication;
use App\Services\WorkforceApplicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkforceApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', WorkforceApplication::class);

        $availableTypes = array_values(array_filter(
            WorkforceApplicationType::cases(),
            fn (WorkforceApplicationType $type): bool => $request->user()->can($type->reviewPermission()->value),
        ));

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::enum(WorkforceApplicationType::class)],
            'status' => ['nullable', Rule::enum(WorkforceApplicationStatus::class)],
        ]);

        $selectedType = $filters['type'] ?? null;
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

        if (filled($filters['q'] ?? null)) {
            $search = '%'.trim($filters['q']).'%';
            $query->where(static function (Builder $applications) use ($search): void {
                $applications->where('application_number', 'like', $search)
                    ->orWhere('position_title', 'like', $search)
                    ->orWhereHas('user', static function (Builder $users) use ($search): void {
                        $users->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
            });
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        return view('admin.workforce-applications.index', [
            'applications' => $query->paginate(20)->withQueryString(),
            'selectedType' => $selectedType,
            'availableTypes' => $availableTypes,
            'filters' => $filters,
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
}
