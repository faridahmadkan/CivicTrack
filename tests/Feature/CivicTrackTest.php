<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CivicTrackTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_routes_are_registered_during_feature_tests(): void
    {
        $this->assertTrue(app('router')->getRoutes()->hasNamedRoute('home'));
        $this->assertTrue(app('router')->getRoutes()->hasNamedRoute('login'));
        $this->assertTrue(app('router')->getRoutes()->hasNamedRoute('admin.dashboard'));
    }

    public function test_citizen_can_register_and_is_assigned_citizen_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Citizen Test',
            'email' => 'citizen@test.local',
            'phone' => '01700000000',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertRedirect(route('citizen.dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'citizen@test.local', 'role' => 'citizen']);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'missing@test.local',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_citizen_and_staff_cannot_access_admin_dashboard(): void
    {
        $citizen = User::factory()->create(['role' => 'citizen']);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($citizen)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($staff)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_citizen_cannot_view_another_citizens_report(): void
    {
        $owner = User::factory()->create(['role' => 'citizen']);
        $other = User::factory()->create(['role' => 'citizen']);
        $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category', 'is_active' => true]);
        $report = Report::create([
            'reference_number' => 'CT-TEST-0001',
            'citizen_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Test issue',
            'description' => 'Test description',
            'location' => 'Test location',
            'priority' => 'medium',
            'status' => 'pending_review',
        ]);

        $this->actingAs($other)->get(route('citizen.reports.show', $report))->assertForbidden();
    }

    public function test_citizen_can_create_report_with_valid_photo(): void
    {
        Storage::fake('local');
        $citizen = User::factory()->create(['role' => 'citizen']);
        $category = Category::create(['name' => 'Roads', 'slug' => 'roads', 'is_active' => true]);

        $response = $this->actingAs($citizen)->post('/citizen/reports', [
            'title' => 'Pothole',
            'description' => 'A large pothole needs repair.',
            'category_id' => $category->id,
            'location' => 'Main Road',
            'latitude' => 23.8103,
            'longitude' => 90.4125,
            'priority' => 'high',
            'photos' => [UploadedFile::fake()->create('evidence.jpg', 100, 'image/jpeg')],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reports', ['title' => 'Pothole', 'status' => 'pending_review']);
        $this->assertDatabaseCount('report_status_histories', 1);
    }

    public function test_invalid_image_is_rejected(): void
    {
        Storage::fake('local');
        $citizen = User::factory()->create(['role' => 'citizen']);
        $category = Category::create(['name' => 'Roads', 'slug' => 'roads', 'is_active' => true]);

        $response = $this->actingAs($citizen)->post('/citizen/reports', [
            'title' => 'Pothole',
            'description' => 'A large pothole needs repair.',
            'category_id' => $category->id,
            'location' => 'Main Road',
            'priority' => 'medium',
            'photos' => [UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream')],
        ]);

        $response->assertSessionHasErrors('photos.0');
    }

    public function test_admin_can_approve_and_assign_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $citizen = User::factory()->create(['role' => 'citizen']);
        $staff = User::factory()->create(['role' => 'staff']);
        $category = Category::create(['name' => 'Roads', 'slug' => 'roads', 'is_active' => true]);
        $report = Report::create([
            'reference_number' => 'CT-TEST-0002',
            'citizen_id' => $citizen->id,
            'category_id' => $category->id,
            'title' => 'Pothole',
            'description' => 'Needs repair',
            'location' => 'Main Road',
            'priority' => 'high',
            'status' => 'pending_review',
        ]);

        $this->actingAs($admin)->post(route('admin.reports.approve', $report))->assertRedirect();
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'approved']);

        $this->actingAs($admin)->post(route('admin.reports.assign', $report), [
            'staff_id' => $staff->id,
            'notes' => 'Inspect and repair.',
        ])->assertRedirect();

        $this->assertDatabaseHas('assignments', ['report_id' => $report->id, 'staff_id' => $staff->id]);
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'assigned']);
    }

    public function test_feedback_requires_resolved_report_and_prevents_duplicates(): void
    {
        $citizen = User::factory()->create(['role' => 'citizen']);
        $category = Category::create(['name' => 'Roads', 'slug' => 'roads', 'is_active' => true]);
        $report = Report::create([
            'reference_number' => 'CT-TEST-0003',
            'citizen_id' => $citizen->id,
            'category_id' => $category->id,
            'title' => 'Resolved issue',
            'description' => 'Test',
            'location' => 'Test',
            'priority' => 'medium',
            'status' => 'resolved',
        ]);

        $this->actingAs($citizen)->post(route('citizen.reports.feedback', $report), [
            'rating' => 5,
            'comment' => 'Excellent work',
        ])->assertRedirect();

        $this->assertDatabaseHas('feedback', ['report_id' => $report->id, 'rating' => 5]);
        $this->actingAs($citizen)->post(route('citizen.reports.feedback', $report), [
            'rating' => 4,
        ])->assertStatus(422);
    }
}
