<?php

namespace App\Http\Controllers;

use App\Enums\ReportAttachmentVisibility;
use App\Models\CivicReport;
use App\Models\ReportAttachment;
use App\Models\TaskEvidence;
use App\Models\WorkforceApplicationDocument;
use App\Models\WorkTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateFileController extends Controller
{
    public function reportAttachment(Request $request, CivicReport $report, ReportAttachment $attachment): StreamedResponse
    {
        return $this->downloadReportAttachment($request, $report, $attachment);
    }

    public function staffReportAttachment(
        Request $request,
        CivicReport $civicReport,
        ReportAttachment $attachment,
    ): StreamedResponse {
        return $this->downloadReportAttachment($request, $civicReport, $attachment);
    }

    private function downloadReportAttachment(
        Request $request,
        CivicReport $report,
        ReportAttachment $attachment,
    ): StreamedResponse {
        abort_unless($attachment->civic_report_id === $report->id, 404);

        $user = $request->user();
        $canViewAsReporter = $attachment->visibility === ReportAttachmentVisibility::Reporter
            && (($user !== null && $user->isActive() && $report->isOwnedBy($user))
                || ($report->user_id === null
                    && $request->session()->get('guest_report_access_'.$report->id) === true));
        $canViewAsStaff = $user !== null
            && $user->isActive()
            && Gate::forUser($user)->allows('viewInternalAttachments', $report);

        abort_unless($canViewAsReporter || $canViewAsStaff, 404);

        return $this->downloadPrivateFile(
            $attachment->storage_disk,
            $attachment->storage_path,
            'civic-reports/'.$report->id,
            $attachment->original_name,
            $attachment->mime_type,
        );
    }

    public function taskEvidence(WorkTask $workTask, TaskEvidence $evidence): StreamedResponse
    {
        abort_unless($evidence->work_task_id === $workTask->id, 404);
        $this->authorize('viewEvidence', [$workTask, $evidence]);

        return $this->downloadTaskEvidence($workTask, $evidence);
    }

    public function staffTaskEvidence(WorkTask $workTask, TaskEvidence $evidence): StreamedResponse
    {
        abort_unless($evidence->work_task_id === $workTask->id, 404);
        $this->authorize('viewStaffEvidence', [$workTask, $evidence]);

        return $this->downloadTaskEvidence($workTask, $evidence);
    }

    private function downloadTaskEvidence(WorkTask $workTask, TaskEvidence $evidence): StreamedResponse
    {

        return $this->downloadPrivateFile(
            $evidence->storage_disk,
            $evidence->storage_path,
            'task-evidence/'.$workTask->id,
            $evidence->original_name,
            $evidence->mime_type,
        );
    }

    public function workforceDocument(Request $request, WorkforceApplicationDocument $document): StreamedResponse
    {
        $application = $document->application;
        abort_unless($document->workforce_application_id === $application->id, 404);
        abort_unless($request->user()?->isActive(), 404);
        $this->authorize('view', $application);

        return $this->downloadPrivateFile(
            $document->storage_disk,
            $document->storage_path,
            'workforce-applications/'.$application->id,
            $document->original_name,
            $document->mime_type,
        );
    }

    private function downloadPrivateFile(
        string $diskName,
        string $storagePath,
        string $requiredPrefix,
        string $originalName,
        string $mimeType,
    ): StreamedResponse {
        abort_unless(in_array($diskName, ['local', 'private'], true), 404);
        abort_unless($this->isGeneratedPathWithin($storagePath, $requiredPrefix), 404);

        $disk = Storage::disk($diskName);
        abort_unless($disk->exists($storagePath), 404);

        $safeOriginalName = preg_replace(
            '/[\x00-\x1F\x7F]/u',
            '',
            basename(str_replace('\\', '/', $originalName)),
        ) ?: 'download';
        $safeMimeType = in_array($mimeType, [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true) ? $mimeType : 'application/octet-stream';

        return $disk->download($storagePath, mb_substr($safeOriginalName, 0, 180), [
            'Content-Type' => $safeMimeType,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function isGeneratedPathWithin(string $storagePath, string $requiredPrefix): bool
    {
        if (! str_starts_with($storagePath, $requiredPrefix.'/')
            || str_contains($storagePath, '\\')
            || str_contains($storagePath, chr(0))) {
            return false;
        }

        foreach (explode('/', $storagePath) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return false;
            }
        }

        return true;
    }
}
