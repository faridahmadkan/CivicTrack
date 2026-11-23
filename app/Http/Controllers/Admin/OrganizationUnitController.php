<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrganizationUnitType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ManageOrganizationUnitRequest;
use App\Models\OrganizationUnit;
use App\Services\OrganizationUnitService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationUnitController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', OrganizationUnit::class);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::enum(OrganizationUnitType::class)],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
        $query = OrganizationUnit::query()
            ->with('parent')
            ->when(filled($filters['q'] ?? null), static function (Builder $units) use ($filters): void {
                $search = '%'.trim($filters['q']).'%';
                $units->where(static function (Builder $matches) use ($search): void {
                    $matches->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhereHas('parent', static fn (Builder $parent) => $parent->where('name', 'like', $search));
                });
            })
            ->when(filled($filters['type'] ?? null), static fn (Builder $units) => $units->where('unit_type', $filters['type']));

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return view('admin.organization-units.index', [
            'organizationUnits' => $query
                ->orderBy('unit_type')
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $filters,
            'activeParents' => OrganizationUnit::query()->where('is_active', true)->orderBy('name')->get(),
            'unitTypes' => OrganizationUnitType::cases(),
        ]);
    }

    public function store(ManageOrganizationUnitRequest $request, OrganizationUnitService $service): RedirectResponse
    {
        $service->create($request->user(), $request->validated());

        return to_route('admin.organization-units.index')->with('status', 'Organization unit created.');
    }

    public function update(
        ManageOrganizationUnitRequest $request,
        OrganizationUnit $organizationUnit,
        OrganizationUnitService $service,
    ): RedirectResponse {
        $service->update($organizationUnit, $request->user(), $request->validated());

        return to_route('admin.organization-units.index')->with('status', 'Organization unit updated.');
    }
}
