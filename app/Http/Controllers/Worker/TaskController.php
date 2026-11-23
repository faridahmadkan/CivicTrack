<?php

namespace App\Http\Controllers\Worker;

use App\Enums\TaskAssignmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitTaskEvidenceRequest;
use App\Http\Requests\WorkerTaskUpdateRequest;
use App\Models\TaskEvidence;
use App\Models\TaskUpdate;
use App\Models\User;
use App\Models\WorkTask;
use App\Services\TaskAssignmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskController extends Controller
{
    public function home(Request $request): View
    {
        $activeAssignments = $request->user()->taskAssignments()
            ->whereNull('ended_at')
            ->whereIn('status', [
                TaskAssignmentStatus::Assigned->value,
                TaskAssignmentStatus::Accepted->value,
                TaskAssignmentStatus::InProgress->value,
                TaskAssignmentStatus::Submitted->value,
                TaskAssignmentStatus::NeedsRework->value,
            ])
            ->with('task.workOrder.report')
            ->orderByDesc('assigned_at')
            ->get();

        return view('worker.home', ['assignments' => $activeAssignments]);
    }

    public function index(Request $request): View
    {
        return $this->home($request);
    }

    public function show(WorkTask $workTask): View
    {
        $this->authorize('view', $workTask);
        $workTask->load([
            'workOrder.report.category', 'activeAssignment.assigner', 'updates.actor',
            'verifications.reviewer', 'dependencies',
        ]);
        $assignment = $workTask->activeAssignment;

        abort_unless($assignment !== null && $assignment->worker_id === auth()->id(), 404);
        $evidence = $workTask->evidence()->where('task_assignment_id', $assignment->id)->get();

        return view('worker.tasks.show', [
            'task' => $workTask,
            'assignment' => $assignment,
            'evidence' => $evidence,
        ]);
    }

    public function accept(Request $request, WorkTask $workTask, TaskAssignmentService $service): RedirectResponse
    {
        $this->authorize('updateOwn', $workTask);
        $service->accept($workTask, $request->user());

        return to_route('worker.tasks.show', $workTask)->with('status', 'Assignment accepted.');
    }

    public function decline(
        WorkerTaskUpdateRequest $request,
        WorkTask $workTask,
        TaskAssignmentService $service,
    ): RedirectResponse {
        $service->decline($workTask, $request->user(), $request->validated('body'));

        return to_route('worker.tasks.index')->with('status', 'Assignment declined and returned to the service unit queue.');
    }

    public function start(Request $request, WorkTask $workTask, TaskAssignmentService $service): RedirectResponse
    {
        $this->authorize('updateOwn', $workTask);
        $service->start($workTask, $request->user());

        return to_route('worker.tasks.show', $workTask)->with('status', 'Work started.');
    }

    public function update(
        WorkerTaskUpdateRequest $request,
        WorkTask $workTask,
        TaskAssignmentService $service,
    ): RedirectResponse {
        $service->addProgressUpdate($workTask, $request->user(), $request->validated('body'));

        return to_route('worker.tasks.show', $workTask)->with('status', 'Progress update saved.');
    }

    public function storeEvidence(
        SubmitTaskEvidenceRequest $request,
        WorkTask $workTask,
        TaskAssignmentService $service,
    ): RedirectResponse {
        $attributes = $request->validated();
        $service->addEvidence(
            $workTask,
            $request->user(),
            $attributes['evidence'],
            $attributes['evidence_type'],
            $attributes['caption'] ?? null,
        );

        return to_route('worker.tasks.show', $workTask)->with('status', 'Evidence uploaded to the private task record.');
    }

    public function submit(Request $request, WorkTask $workTask, TaskAssignmentService $service): RedirectResponse
    {
        $this->authorize('updateOwn', $workTask);
        $service->submitForVerification($workTask, $request->user());

        return to_route('worker.tasks.show', $workTask)->with('status', 'Task submitted to staff for verification.');
    }

    public function downloadEvidence(Request $request, WorkTask $workTask, TaskEvidence $evidence): StreamedResponse
    {
        $this->authorize('view', $workTask);
        $assignment = $workTask->activeAssignment;
        abort_unless(
            $assignment !== null
                && $assignment->worker_id === $request->user()->id
                && $evidence->work_task_id === $workTask->id
                && $evidence->task_assignment_id === $assignment->id,
            404,
        );
        abort_unless(Storage::disk($evidence->storage_disk)->exists($evidence->storage_path), 404);

        return Storage::disk($evidence->storage_disk)->download(
            $evidence->storage_path,
            $evidence->original_name,
            ['Content-Type' => $evidence->mime_type, 'X-Content-Type-Options' => 'nosniff'],
        );
    }
}
