<?php

namespace Tests\Feature\Reports;

use App\Enums\CivicReportStatus;
use App\Enums\RoleName;
use App\Models\CivicReport;
use App\Models\GuestAccessToken;
use App\Models\ReportAttachment;
use App\Models\ReportStatusEvent;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CivicReportSubmissionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_can_submit_and_track_a_private_report_with_a_hashed_access_code(): void
    {
        Storage::fake('private');
        $this->seed();
        $category = ServiceCategory::query()->where('code', 'ROAD')->firstOrFail();
        $document = UploadedFile::fake()->create('crossing.jpg', 100, 'image/jpeg');

        $response = $this->post(route('public.reports.store'), [
            'service_category_id' => $category->id,
            'title' => 'Damaged crossing sign on Oak Street',
            'description' => 'The crossing sign fell over near the north entrance this morning.',
            'latitude' => '38.8977000',
            'longitude' => '-77.0365000',
            'address' => 'Oak Street near the north entrance',
            'guest_email' => 'resident@example.test',
            'documents' => [$document],
        ]);

        $report = CivicReport::query()->firstOrFail();
        $token = $response->getSession()->get('guest_access_token');

        $response->assertRedirect(route('public.reports.submitted', $report));
        $this->assertIsString($token);
        $this->assertSame(CivicReportStatus::Submitted, $report->status);
        $this->assertNull($report->user_id);
        $this->assertSame('resident@example.test', $report->guest_contact_email);
        $this->assertDatabaseHas('report_status_events', [
            'civic_report_id' => $report->id,
            'event_type' => 'submitted',
            'to_status' => CivicReportStatus::Submitted->value,
        ]);
        $this->assertDatabaseHas('routing_decisions', [
            'civic_report_id' => $report->id,
            'source' => 'unassigned_queue',
        ]);

        $storedToken = GuestAccessToken::query()->firstOrFail();
        $this->assertNotSame($token, $storedToken->token_hash);
        $this->assertSame(hash('sha256', $token), $storedToken->token_hash);

        $attachment = ReportAttachment::query()->firstOrFail();
        Storage::disk('private')->assertExists($attachment->storage_path);
        $this->assertArrayNotHasKey('storage_path', $attachment->toArray());
        $this->assertDatabaseCount('audit_events', 2);

        $this->get(route('public.reports.submitted', $report))
            ->assertOk()
            ->assertSee($report->public_reference)
            ->assertSee($token);

        $this->get(route('public.reports.track.show', $report))
            ->assertRedirect(route('public.reports.access', $report));

        $this->post(route('public.reports.verify-access', $report), ['token' => $token])
            ->assertRedirect(route('public.reports.track.show', $report));

        $this->get(route('public.reports.track.show', $report))
            ->assertOk()
            ->assertSee($report->public_reference)
            ->assertSee('Damaged crossing sign on Oak Street');
    }

    public function test_report_reference_alone_does_not_grant_access_to_guest_report(): void
    {
        $this->seed();
        $report = CivicReport::factory()->create();
        $attachment = ReportAttachment::factory()->create(['civic_report_id' => $report->id]);

        $this->get(route('public.reports.track.show', $report))
            ->assertRedirect(route('public.reports.access', $report));

        $this->get(route('public.reports.track.attachments.download', [$report, $attachment]))
            ->assertNotFound();
    }

    public function test_registered_citizen_can_view_only_their_own_reports(): void
    {
        $this->seed();
        $owner = User::factory()->create();
        $owner->assignRole(RoleName::Citizen->value);
        $owner->markEmailAsVerified();
        $otherCitizen = User::factory()->create();
        $otherCitizen->assignRole(RoleName::Citizen->value);
        $report = CivicReport::factory()->create(['user_id' => $owner->id]);
        ReportStatusEvent::factory()->create([
            'civic_report_id' => $report->id,
            'to_status' => CivicReportStatus::Submitted->value,
        ]);

        $this->actingAs($owner)
            ->get(route('citizen.reports.show', $report))
            ->assertOk()
            ->assertSee($report->public_reference);

        $this->actingAs($otherCitizen)
            ->get(route('citizen.reports.show', $report))
            ->assertNotFound();
    }
}
