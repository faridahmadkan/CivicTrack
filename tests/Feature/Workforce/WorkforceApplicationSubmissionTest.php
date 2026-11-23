<?php

namespace Tests\Feature\Workforce;

use App\Enums\RoleName;
use App\Enums\WorkforceApplicationStatus;
use App\Enums\WorkforceApplicationType;
use App\Models\AuditEvent;
use App\Models\OrganizationUnit;
use App\Models\RegistrationControl;
use App\Models\User;
use App\Models\WorkforceApplication;
use App\Models\WorkforceApplicationDocument;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkforceApplicationSubmissionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_open_staff_application_is_private_and_does_not_grant_workforce_access(): void
    {
        Storage::fake('private');
        $this->seed();
        RegistrationControl::query()->where('context', 'staff')->update(['enabled' => true]);

        $organizationUnit = OrganizationUnit::factory()->create();
        $applicant = User::factory()->create();
        $applicant->assignRole(RoleName::Citizen->value);
        $document = UploadedFile::fake()->create('service-reference.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($applicant)->post(route('workforce.applications.store', 'staff'), [
            'position_title' => 'Community services coordinator',
            'requested_unit_id' => $organizationUnit->id,
            'experience_summary' => 'Coordinated public-facing services for five years.',
            'qualification_summary' => 'Public administration diploma.',
            'documents' => [$document],
        ]);

        $application = WorkforceApplication::query()->firstOrFail();

        $response->assertRedirect(route('workforce.applications.show', $application));
        $this->assertSame(WorkforceApplicationType::Staff, $application->application_type);
        $this->assertSame(WorkforceApplicationStatus::Submitted, $application->status);
        $this->assertSame('CT-ST-', substr($application->application_number, 0, 6));
        $this->assertTrue($applicant->fresh()->hasRole(RoleName::Citizen->value));
        $this->assertFalse($applicant->fresh()->hasRole(RoleName::Staff->value));
        $this->assertFalse($applicant->fresh()->hasRole(RoleName::Worker->value));
        $this->assertDatabaseHas('workforce_application_events', [
            'workforce_application_id' => $application->id,
            'event_type' => 'submitted',
            'to_status' => WorkforceApplicationStatus::Submitted->value,
        ]);
        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $applicant->id,
            'event_key' => 'workforce_application.submitted',
            'subject_id' => $application->id,
        ]);

        $storedDocument = WorkforceApplicationDocument::query()->firstOrFail();
        Storage::disk('private')->assertExists($storedDocument->storage_path);
        $this->assertArrayNotHasKey('storage_path', $storedDocument->toArray());
        $this->assertSame(1, AuditEvent::query()->count());

        $this->actingAs($applicant)
            ->get(route('workforce.applications.show', $application))
            ->assertOk()
            ->assertSee($application->application_number)
            ->assertSee('aria-current="page"', false)
            ->assertSee('service-reference.jpg');
    }

    public function test_closed_workforce_registration_creates_no_application_or_document(): void
    {
        Storage::fake('private');
        $this->seed();
        RegistrationControl::query()->where('context', 'worker')->update(['enabled' => false]);

        $organizationUnit = OrganizationUnit::factory()->create();
        $applicant = User::factory()->create();
        $applicant->assignRole(RoleName::Citizen->value);

        $response = $this->actingAs($applicant)
            ->from(route('workforce.applications.create', 'worker'))
            ->post(route('workforce.applications.store', 'worker'), [
                'position_title' => 'Field maintenance worker',
                'requested_unit_id' => $organizationUnit->id,
            ]);

        $response->assertRedirect(route('workforce.applications.create', 'worker'))
            ->assertSessionHasErrors('registration');
        $this->assertDatabaseCount('workforce_applications', 0);
        $this->assertDatabaseCount('workforce_application_documents', 0);
        $this->assertDatabaseCount('audit_events', 0);
        $this->assertAuthenticatedAs($applicant);
    }

    public function test_another_citizen_cannot_view_a_private_application(): void
    {
        $this->seed();
        $owner = User::factory()->create();
        $owner->assignRole(RoleName::Citizen->value);
        $otherCitizen = User::factory()->create();
        $otherCitizen->assignRole(RoleName::Citizen->value);
        $application = WorkforceApplication::factory()->staff()->create(['user_id' => $owner->id]);

        $this->actingAs($otherCitizen)
            ->get(route('workforce.applications.show', $application))
            ->assertNotFound();
    }
}
