<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceSearchRequest;
use App\Services\PlaceSearchService;
use Illuminate\Http\JsonResponse;

class PlaceSearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(PlaceSearchRequest $request, PlaceSearchService $placeSearch): JsonResponse
    {
        $results = $placeSearch->search($request->validated('q'));

        if ($results === null) {
            $searchIsAvailable = config('civictrack.map.place_search.enabled')
                && config('civictrack.map.place_search.configured');
            $message = $searchIsAvailable
                ? 'Place search is temporarily unavailable. Please try again later.'
                : 'Place search is not configured.';

            return response()->json(['message' => $message], 503);
        }

        return response()->json(['results' => $results]);
    }
}
