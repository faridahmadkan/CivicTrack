<?php

namespace App\Http\Controllers\Staff;

use App\Enums\TaskVerificationDecision;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignTaskRequest;
use App\Http\Requests\VerifyTaskRequest;
use App\Models\User;
use App\Models\WorkTask;
use App\Services\TaskAssignmentService;
use App\Services\TaskVerificationService;
use Illuminate\Http\RedirectResponse;

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
            $request->validated('replace_assignment_id') === null
                ? null
                : (int) $request->validated('replace_assignment_id'),
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
}
