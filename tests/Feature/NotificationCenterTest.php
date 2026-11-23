<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\CivicReport;
use App\Models\User;
use App\Notifications\CivicReportStatusChangedNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_citizen_can_open_and_mark_their_own_service_notification_read(): void
    {
        $this->seed();
        $citizen = User::factory()->create();
        $citizen->assignRole(RoleName::Citizen->value);
        $citizen->markEmailAsVerified();
        $report = CivicReport::factory()->create(['user_id' => $citizen->id]);
        $citizen->notify(new CivicReportStatusChangedNotification($report));
        $notification = $citizen->notifications()->firstOrFail();

        $this->actingAs($citizen)
            ->get(route('account.notifications.index', ['q' => $report->public_reference, 'status' => 'unread']))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee($report->public_reference)
            ->assertSee('Unread');

        $this->actingAs($citizen)
            ->patch(route('account.notifications.read', $notification->id))
            ->assertRedirect(route('account.notifications.index'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_a_user_cannot_mark_another_accounts_notification_read(): void
    {
        $this->seed();
        $owner = User::factory()->create();
        $owner->assignRole(RoleName::Citizen->value);
        $owner->markEmailAsVerified();
        $otherUser = User::factory()->create();
        $otherUser->assignRole(RoleName::Citizen->value);
        $otherUser->markEmailAsVerified();
        $report = CivicReport::factory()->create(['user_id' => $owner->id]);
        $owner->notify(new CivicReportStatusChangedNotification($report));
        $notification = $owner->notifications()->firstOrFail();

        $this->actingAs($otherUser)
            ->patch(route('account.notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }
}
