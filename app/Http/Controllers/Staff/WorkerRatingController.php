<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageWorkerRatingRequest;
use App\Models\TaskAssignment;
use App\Models\WorkTask;
use App\Services\WorkerRatingService;
use Illuminate\Http\RedirectResponse;

class WorkerRatingController extends Controller
{
    public function store(
        ManageWorkerRatingRequest $request,
        WorkTask $workTask,
        TaskAssignment $taskAssignment,
        WorkerRatingService $service,
    ): RedirectResponse {
        abort_unless($taskAssignment->work_task_id === $workTask->id, 404);
        $service->rate($taskAssignment, $request->user(), $request->validated());

        return to_route('staff.work-orders.show', $workTask->workOrder)
            ->with('status', 'Worker feedback has been recorded.');
    }
}
