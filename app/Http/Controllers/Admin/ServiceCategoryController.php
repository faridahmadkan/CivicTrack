<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageServiceCategoryRequest;
use App\Models\OrganizationUnit;
use App\Models\ServiceCategory;
use App\Services\ServiceCatalogService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'visibility' => ['nullable', Rule::in(['public', 'hidden'])],
        ]);
        $query = ServiceCategory::query()
            ->with('defaultUnit')
            ->when(filled($filters['q'] ?? null), static function (Builder $categories) use ($filters): void {
                $search = '%'.trim($filters['q']).'%';
                $categories->where(static function (Builder $matches) use ($search): void {
                    $matches->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhereHas('defaultUnit', static fn (Builder $unit) => $unit->where('name', 'like', $search));
                });
            });

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        if (($filters['visibility'] ?? null) === 'public') {
            $query->where('is_public', true);
        } elseif (($filters['visibility'] ?? null) === 'hidden') {
            $query->where('is_public', false);
        }

        return view('admin.service-categories.index', [
            'categories' => $query
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $filters,
            'organizationUnits' => OrganizationUnit::query()
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
