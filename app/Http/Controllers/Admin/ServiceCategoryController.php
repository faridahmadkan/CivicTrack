<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageServiceCategoryRequest;
use App\Models\ServiceCategory;
use App\Models\UnitMembership;
use App\Services\ServiceCatalogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.service-categories.index', [
            'categories' => ServiceCategory::query()
                ->with('defaultUnit')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'organizationUnits' => \App\Models\OrganizationUnit::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(ManageServiceCategoryRequest $request, ServiceCatalogService $service): RedirectResponse
    {
        $service->save(null, $request->validated(), $request->user());

        return to_route('admin.service-categories.index')->with('status', 'Service category created.');
    }

    public function update(
        ManageServiceCategoryRequest $request,
        ServiceCategory $serviceCategory,
        ServiceCatalogService $service,
    ): RedirectResponse {
        $service->save($serviceCategory, $request->validated(), $request->user());

        return to_route('admin.service-categories.index')->with('status', 'Service category updated.');
    }
}
