<?php

namespace Tests\Feature\Workforce;

use App\Enums\WorkforceApplicationStatus;
use App\Events\WorkforceApplicationReviewed;
use App\Listeners\NotifyWorkforceApplicant;
use App\Models\User;
use App\Models\WorkforceApplication;
use App\Notifications\WorkforceApplicationStatusChangedNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WorkforceApplicationNotificationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_status_change_notification_reaches_the_applicant_by_mail_and_in_app_channels(): void
    {
        $applicant = User::factory()->create();
        $application = WorkforceApplication::factory()->staff()->create([
            'user_id' => $applicant->id,
            'status' => WorkforceApplicationStatus::InformationRequested,
            'requested_information' => 'Please provide your relevant certificate.',
        ]);
        Notification::fake();

        app(NotifyWorkforceApplicant::class)->handle(new WorkforceApplicationReviewed($application));

        Notification::assertSentTo(
            $applicant,
            WorkforceApplicationStatusChangedNotification::class,
            function (WorkforceApplicationStatusChangedNotification $notification, array $channels) use ($applicant, $application): bool {
                $mail = $notification->toMail($applicant);
                $data = $notification->toArray($applicant);

                $this->assertSame(['database', 'mail'], $channels);
                $this->assertSame($application->application_number, $data['application_number']);
                $this->assertSame(WorkforceApplicationStatus::InformationRequested->value, $data['status']);
                $this->assertStringContainsString('Please provide your relevant certificate.', implode(' ', $mail->introLines));
                $this->assertSame(route('workforce.applications.show', $application), $mail->actionUrl);

                return true;
            },
        );
    }
}
