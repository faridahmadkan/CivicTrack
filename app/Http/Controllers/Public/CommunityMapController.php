<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Enums\CivicReportStatus;
use App\Http\Requests\PublicMapFilterRequest;
use App\Models\CivicReport;
use App\Models\ServiceCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class CommunityMapController extends Controller
{
    public function __invoke(PublicMapFilterRequest $request): View
    {
        $filters = $request->validated();
        $reportsQuery = CivicReport::query()
            ->where('is_public', true)
            ->whereIn('status', [
                CivicReportStatus::Submitted->value,
                CivicReportStatus::UnderReview->value,
                CivicReportStatus::NeedsInformation->value,
                CivicReportStatus::Acknowledged->value,
                CivicReportStatus::InProgress->value,
                CivicReportStatus::Resolved->value,
                CivicReportStatus::Reopened->value,
            ])
            ->whereHas('location', static fn (Builder $location): Builder => $location
                ->where('privacy_level', 'generalized_public')
                ->whereNotNull('public_latitude')
                ->whereNotNull('public_longitude'))
            ->with(['category', 'location'])
            ->orderByDesc('submitted_at');

        if (filled($filters['category_id'] ?? null)) {
            $reportsQuery->where('service_category_id', $filters['category_id']);
        }

        $reports = $reportsQuery->limit(500)->get();

        return view('public.map.index', [
            'reports' => $reports,
            'mapMarkers' => $reports->map(static fn (CivicReport $report): array => [
                'latitude' => (float) $report->location->public_latitude,
                'longitude' => (float) $report->location->public_longitude,
                'category' => $report->category->name,
                'status' => $report->status->label(),
            ])->values(),
            'categories' => ServiceCategory::query()
                ->where('is_active', true)
                ->where('is_public', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'filters' => $filters,
            'mapCenter' => [config('civictrack.map.latitude'), config('civictrack.map.longitude')],
            'tileUrl' => config('civictrack.map.tile_url'),
            'mapAttribution' => config('civictrack.map.attribution'),
        ]);
    }
}
