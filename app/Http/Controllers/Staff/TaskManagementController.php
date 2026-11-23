<?php

namespace App\Http\Controllers\Staff;

use App\Enums\TaskVerificationDecision;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignTaskRequest;
use App\Http\Requests\VerifyTaskRequest;
use App\Models\TaskEvidence;
use App\Models\User;
use App\Models\WorkTask;
use App\Services\TaskAssignmentService;
use App\Services\TaskVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskManagementController extends Controller
{
    public function assign(
        AssignTaskRequest $request,
        WorkTask $workTask,
        TaskAssignmentService $service,
    ): RedirectResponse {
        $worker = User::query()->findOrFail($request->validated('worker_id'));
        $service->assign(
            $workTask,
            $worker,
            $request->user(),
            $request->validated('assignment_instructions'),
            $request->validated('reason'),
        );

        return to_route('staff.work-orders.show', $workTask->work_order_id)->with('status', 'Task assignment recorded.');
    }

    public function verify(
        VerifyTaskRequest $request,
        WorkTask $workTask,
        TaskVerificationService $service,
    ): RedirectResponse {
        $service->review(
            $workTask,
            $request->user(),
            TaskVerificationDecision::from($request->validated('decision')),
            $request->validated('reason'),
        );

        return to_route('staff.work-orders.show', $workTask->work_order_id)->with('status', 'Verification decision recorded.');
    }

    public function downloadEvidence(WorkTask $workTask, TaskEvidence $evidence): StreamedResponse
    {
        $this->authorize('view', $workTask);
        abort_unless($evidence->work_task_id === $workTask->id, 404);
        abort_unless(Storage::disk($evidence->storage_disk)->exists($evidence->storage_path), 404);

        return Storage::disk($evidence->storage_disk)->download(
            $evidence->storage_path,
            $evidence->original_name,
            ['Content-Type' => $evidence->mime_type, 'X-Content-Type-Options' => 'nosniff'],
        );
    }
}
