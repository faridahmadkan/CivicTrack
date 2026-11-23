<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PublicPlaceSearchTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('civictrack.map.place_search.url', 'https://nominatim.openstreetmap.org/search');
        RateLimiter::clear('civictrack:place-search:nominatim');
    }

    public function test_search_sends_only_the_place_query_and_returns_validated_public_results(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/search*' => Http::response([
                [
                    'display_name' => 'Oak Street, Central Ward',
                    'lat' => '38.8977',
                    'lon' => '-77.0365',
                ],
                [
                    'display_name' => 'Invalid coordinate result',
                    'lat' => '120',
                    'lon' => '500',
                ],
                ['display_name' => 'Missing coordinates'],
            ]),
        ]);

        $this->postJson(route('public.places.search'), ['q' => 'Oak Street'])
            ->assertOk()
            ->assertExactJson([
                'results' => [[
                    'label' => 'Oak Street, Central Ward',
                    'latitude' => 38.8977,
                    'longitude' => -77.0365,
                ]],
            ]);

        Http::assertSent(function (ClientRequest $request): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return str_contains($request->url(), 'nominatim.openstreetmap.org/search')
                && $request->hasHeader('User-Agent', 'CivicTrack/3.0 ('.rtrim((string) config('app.url'), '/').')')
                && $request->hasHeader('Referer')
                && $query === ['format' => 'jsonv2', 'q' => 'Oak Street', 'limit' => '5'];
        });
    }

    public function test_search_can_return_no_results_without_exposing_report_coordinates(): void
    {
        Http::fake(['https://nominatim.openstreetmap.org/search*' => Http::response([])]);

        $this->postJson(route('public.places.search'), ['q' => 'No such public place'])
            ->assertOk()
            ->assertExactJson(['results' => []]);
    }

    public function test_coordinate_queries_are_rejected_before_reaching_the_provider(): void
    {
        Http::fake();

        $this->postJson(route('public.places.search'), ['q' => '38.8977, -77.0365'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');

        Http::assertNothingSent();
    }

    public function test_provider_failures_show_a_service_unavailable_state(): void
    {
        Http::fake(['https://nominatim.openstreetmap.org/search*' => Http::response([], 503)]);

        $this->postJson(route('public.places.search'), ['q' => 'Provider outage'])
            ->assertServiceUnavailable()
            ->assertJsonPath('message', 'Place search is temporarily unavailable. Please try again later.');
    }

    public function test_place_search_can_be_disabled_without_making_a_provider_request(): void
    {
        config()->set('civictrack.map.place_search.enabled', false);
        Http::fake();

        $this->postJson(route('public.places.search'), ['q' => 'Oak Street'])
            ->assertServiceUnavailable()
            ->assertJsonPath('message', 'Place search is not configured.');

        Http::assertNothingSent();
    }

    public function test_provider_requests_are_limited_to_one_per_second_across_queries(): void
    {
        Http::fake(['https://nominatim.openstreetmap.org/search*' => Http::response([])]);

        $this->postJson(route('public.places.search'), ['q' => 'First public place'])->assertOk();
        $this->postJson(route('public.places.search'), ['q' => 'Second public place'])
            ->assertTooManyRequests()
            ->assertHeader('Retry-After', '1');

        Http::assertSentCount(1);
    }

    public function test_report_location_search_waits_for_confirmation_and_explains_its_privacy_boundary(): void
    {
        $this->seed();

        $response = $this->get(route('public.reports.create'))
            ->assertOk()
            ->assertSee('data-report-location-search', false)
            ->assertSee('data-confirm-search', false)
            ->assertSee('Use this location')
            ->assertSee('Your report pin coordinates are not sent.')
            ->assertSee(route('public.places.search'));

        $this->assertSame(1, substr_count($response->getContent(), '<form'));
    }
}
