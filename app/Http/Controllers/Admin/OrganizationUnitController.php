<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrganizationUnitType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ManageOrganizationUnitRequest;
use App\Models\OrganizationUnit;
use App\Services\OrganizationUnitService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class OrganizationUnitController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', OrganizationUnit::class);

        return view('admin.organization-units.index', [
            'organizationUnits' => OrganizationUnit::query()
                ->with('parent')
                ->orderBy('unit_type')
                ->orderBy('name')
                ->get(),
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
