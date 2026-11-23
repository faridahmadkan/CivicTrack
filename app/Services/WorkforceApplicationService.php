<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Enums\WorkforceApplicationStatus;
use App\Enums\WorkforceApplicationType;
use App\Enums\WorkforceMembershipStatus;
use App\Events\WorkforceApplicationReviewed;
use App\Models\OrganizationUnit;
use App\Models\StaffProfile;
use App\Models\UnitMembership;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkforceApplication;
use App\Models\WorkforceApplicationDocument;
use App\Models\WorkforceApplicationEvent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class WorkforceApplicationService
{
    public function __construct(
        private readonly RegistrationControlService $registrationControls,
        private readonly AuditWriter $auditWriter,
    ) {}

    /**
     * @param  array{position_title: string, requested_unit_id: int|string, experience_summary?: string|null, qualification_summary?: string|null}  $attributes
     * @param  array<int, UploadedFile>  $documents
     */
    public function submit(User $applicant, WorkforceApplicationType $type, array $attributes, array $documents): WorkforceApplication
    {
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($applicant, $type, $attributes, $documents, &$storedPaths): WorkforceApplication {
                $applicant = User::query()->lockForUpdate()->findOrFail($applicant->id);

                if (! $applicant->isActive() || ! $applicant->hasRole(RoleName::Citizen->value)) {
                    throw ValidationException::withMessages(['application' => 'An active citizen account is required to apply.']);
                }

                if ($applicant->hasAnyRole([RoleName::Staff->value, RoleName::Worker->value])) {
                    throw ValidationException::withMessages(['application' => 'An account with an approved workforce role cannot submit another workforce application.']);
                }

                if (WorkforceApplication::query()
                    ->where('user_id', $applicant->id)
                    ->whereIn('status', [
                        WorkforceApplicationStatus::Submitted->value,
                        WorkforceApplicationStatus::UnderReview->value,
                        WorkforceApplicationStatus::InformationRequested->value,
                    ])
                    ->exists()) {
                    throw ValidationException::withMessages(['application' => 'You already have a workforce application awaiting a decision.']);
                }

                $this->registrationControls->lockOpenContext($type->registrationRole());

                $organizationUnit = OrganizationUnit::query()
                    ->whereKey($attributes['requested_unit_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if ($organizationUnit === null) {
                    throw ValidationException::withMessages(['requested_unit_id' => 'Choose an active organization unit.']);
                }

                $applicationPrefix = $type === WorkforceApplicationType::Staff ? 'ST' : 'WK';
                $application = WorkforceApplication::query()->create([
                    'user_id' => $applicant->id,
                    'requested_unit_id' => $organizationUnit->id,
                    'application_number' => 'CT-'.$applicationPrefix.'-'.Str::upper((string) Str::ulid()),
                    'application_type' => $type,
                    'status' => WorkforceApplicationStatus::Submitted,
                    'position_title' => $attributes['position_title'],
                    'experience_summary' => $attributes['experience_summary'] ?? null,
                    'qualification_summary' => $attributes['qualification_summary'] ?? null,
                    'submitted_at' => now(),
                ]);

                foreach ($documents as $document) {
                    $this->storeDocument($application, $applicant, $document, $storedPaths);
                }

                $this->recordStatusEvent($application, $applicant, 'submitted', null, WorkforceApplicationStatus::Submitted, null);
                $this->auditWriter->record(
                    'workforce_application.submitted',
                    $application,
                    ['application_type' => $type->value],
                    $applicant,
                );

                return $application;
            });
        } catch (Throwable $exception) {
            if ($storedPaths !== []) {
                Storage::disk('private')->delete($storedPaths);
            }

            throw $exception;
        }
    }

    public function review(
        WorkforceApplication $application,
        User $reviewer,
        WorkforceApplicationStatus $newStatus,
        ?string $reason,
    ): WorkforceApplication {
        if (! $reviewer->can($application->application_type->reviewPermission()->value)) {
            throw ValidationException::withMessages(['status' => 'You are not authorized to review this application type.']);
        }

        $application = DB::transaction(function () use ($application, $reviewer, $newStatus, $reason): WorkforceApplication {
            $application = WorkforceApplication::query()->lockForUpdate()->findOrFail($application->id);

            if (! in_array($application->status, [
                WorkforceApplicationStatus::Submitted,
                WorkforceApplicationStatus::UnderReview,
            ], true)) {
                throw ValidationException::withMessages(['status' => 'This application is no longer available for review.']);
            }

            if (! in_array($newStatus, [
                WorkforceApplicationStatus::UnderReview,
                WorkforceApplicationStatus::InformationRequested,
                WorkforceApplicationStatus::Approved,
                WorkforceApplicationStatus::Rejected,
            ], true)) {
                throw ValidationException::withMessages(['status' => 'Choose a valid review decision.']);
            }

            if (in_array($newStatus, [WorkforceApplicationStatus::InformationRequested, WorkforceApplicationStatus::Rejected], true)
                && trim((string) $reason) === '') {
                throw ValidationException::withMessages(['reason' => 'A reason is required for this decision.']);
            }

            $previousStatus = $application->status;
            $applicant = User::query()->lockForUpdate()->findOrFail($application->user_id);

            if ($newStatus === WorkforceApplicationStatus::Approved) {
                $this->approveApplication($application, $applicant, $reviewer);
            }

            $application->forceFill([
                'status' => $newStatus,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'requested_information' => $newStatus === WorkforceApplicationStatus::InformationRequested ? trim((string) $reason) : null,
                'decision_reason' => $newStatus === WorkforceApplicationStatus::Rejected ? trim((string) $reason) : null,
            ])->save();

            $this->recordStatusEvent($application, $reviewer, 'review_decision', $previousStatus, $newStatus, $reason);
            $this->auditWriter->record(
                'workforce_application.reviewed',
                $application,
                ['application_type' => $application->application_type->value, 'status' => $newStatus->value],
                $reviewer,
            );

            return $application;
        });

        WorkforceApplicationReviewed::dispatch($application);

        return $application;
    }

    /**
     * @param  array{additional_information: string}  $attributes
     * @param  array<int, UploadedFile>  $documents
     */
    public function provideRequestedInformation(
        WorkforceApplication $application,
        User $applicant,
        array $attributes,
        array $documents,
    ): WorkforceApplication {
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($application, $applicant, $attributes, $documents, &$storedPaths): WorkforceApplication {
                $application = WorkforceApplication::query()->lockForUpdate()->findOrFail($application->id);

                if (! $application->isOwnedBy($applicant)) {
                    throw new ModelNotFoundException;
                }

                if ($application->status !== WorkforceApplicationStatus::InformationRequested) {
                    throw ValidationException::withMessages(['application' => 'This application is not waiting for additional information.']);
                }

                $previousStatus = $application->status;
                $application->forceFill([
                    'additional_information' => trim($attributes['additional_information']),
                    'status' => WorkforceApplicationStatus::Submitted,
                    'requested_information' => null,
                ])->save();

                foreach ($documents as $document) {
                    $this->storeDocument($application, $applicant, $document, $storedPaths);
                }

                $this->recordStatusEvent(
                    $application,
                    $applicant,
                    'information_provided',
                    $previousStatus,
                    WorkforceApplicationStatus::Submitted,
                    null,
                );
                $this->auditWriter->record(
                    'workforce_application.information_provided',
                    $application,
                    ['application_type' => $application->application_type->value],
                    $applicant,
                );

                return $application;
            });
        } catch (Throwable $exception) {
            if ($storedPaths !== []) {
                Storage::disk('private')->delete($storedPaths);
            }

            throw $exception;
        }
    }

    /**
     * @param  array<int, string>  $storedPaths
     */
    private function storeDocument(
        WorkforceApplication $application,
        User $uploader,
        UploadedFile $document,
        array &$storedPaths,
    ): WorkforceApplicationDocument {
        $path = $document->store('workforce-applications/'.$application->id, 'private');

        if ($path === false) {
            throw new RuntimeException('The application document could not be stored.');
        }

        $storedPaths[] = $path;
        $hash = hash_file('sha256', $document->getRealPath());

        if ($hash === false) {
            throw new RuntimeException('The application document could not be verified.');
        }

        $originalName = preg_replace('/[\x00-\x1F\x7F]/u', '', basename($document->getClientOriginalName())) ?: 'document';

        return $application->documents()->create([
            'uploaded_by' => $uploader->id,
            'storage_disk' => 'private',
            'storage_path' => $path,
            'original_name' => Str::limit($originalName, 180, ''),
            'mime_type' => $document->getMimeType() ?? 'application/octet-stream',
            'size_bytes' => $document->getSize() ?? 0,
            'sha256' => $hash,
            'document_type' => 'supporting',
        ]);
    }

    private function approveApplication(WorkforceApplication $application, User $applicant, User $reviewer): void
    {
        if (! $applicant->isActive() || $applicant->hasAnyRole([RoleName::Staff->value, RoleName::Worker->value])) {
            throw ValidationException::withMessages(['status' => 'This applicant is not eligible for an additional workforce role.']);
        }

        $organizationUnit = OrganizationUnit::query()
            ->whereKey($application->requested_unit_id)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if ($organizationUnit === null) {
            throw ValidationException::withMessages(['requested_unit_id' => 'The requested organization unit is not active.']);
        }

        if ($application->application_type === WorkforceApplicationType::Staff) {
            StaffProfile::query()->create([
                'user_id' => $applicant->id,
                'approved_application_id' => $application->id,
                'approved_by' => $reviewer->id,
                'position_title' => $application->position_title,
                'approved_at' => now(),
            ]);
        } else {
            WorkerProfile::query()->create([
                'user_id' => $applicant->id,
                'approved_application_id' => $application->id,
                'approved_by' => $reviewer->id,
                'position_title' => $application->position_title,
                'experience_summary' => $application->experience_summary,
                'qualification_summary' => $application->qualification_summary,
                'approved_at' => now(),
            ]);
        }

        $applicant->assignRole($application->application_type->role()->value);
        UnitMembership::query()->create([
            'user_id' => $applicant->id,
            'organization_unit_id' => $organizationUnit->id,
            'created_by' => $reviewer->id,
            'membership_type' => $application->application_type,
            'status' => WorkforceMembershipStatus::Active,
            'position_title' => $application->position_title,
            'started_at' => now(),
        ]);
    }

    private function recordStatusEvent(
        WorkforceApplication $application,
        User $actor,
        string $eventType,
        ?WorkforceApplicationStatus $fromStatus,
        WorkforceApplicationStatus $toStatus,
        ?string $reason,
    ): void {
        WorkforceApplicationEvent::query()->create([
            'workforce_application_id' => $application->id,
            'actor_id' => $actor->id,
            'event_type' => $eventType,
            'from_status' => $fromStatus?->value,
            'to_status' => $toStatus->value,
            'reason' => $reason,
            'properties' => ['application_type' => $application->application_type->value],
            'occurred_at' => now(),
        ]);
    }
}
