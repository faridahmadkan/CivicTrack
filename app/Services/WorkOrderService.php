<?php

namespace App\Services;

use App\Enums\CivicReportPriority;
use App\Enums\CivicReportStatus;
use App\Enums\PermissionName;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkTaskStatus;
use App\Enums\RoleName;
use App\Models\CivicReport;
use App\Models\TaskDependency;
use App\Models\UnitMembership;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkTask;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WorkOrderService
{
    public function __construct(private readonly AuditWriter $auditWriter) {}

    /**
     * @param array{title: string, instructions?: string|null, due_at?: string|null, tasks: array<int, array{title: string, instructions?: string|null, priority: string, due_at?: string|null, depends_on?: array<int, int|string>}>} $attributes
     */
    public function createForReport(CivicReport $report, User $actor, array $attributes): WorkOrder
    {
        $this->assertDependencyGraphIsAcyclic($attributes['tasks']);

        return DB::transaction(function () use ($report, $actor, $attributes): WorkOrder {
            $report = CivicReport::query()->lockForUpdate()->findOrFail($report->id);
            $this->authorizeCreate($report, $actor);

            if ($report->status !== CivicReportStatus::Acknowledged) {
                throw ValidationException::withMessages(['report' => 'A staff member must acknowledge the report before work can be planned.']);
            }

            if ($report->routed_unit_id === null) {
                throw ValidationException::withMessages(['report' => 'Route this report to an active service unit before creating work.']);
            }

            $workOrder = WorkOrder::query()->create([
                'civic_report_id' => $report->id,
                'organization_unit_id' => $report->routed_unit_id,
                'created_by' => $actor->id,
                'reference' => 'CT-WO-'.Str::upper((string) Str::ulid()),
                'title' => trim($attributes['title']),
                'instructions' => isset($attributes['instructions']) ? trim($attributes['instructions']) : null,
                'status' => WorkOrderStatus::Open,
                'due_at' => $attributes['due_at'] ?? null,
            ]);

            $tasksByIndex = [];

            foreach (array_values($attributes['tasks']) as $index => $taskAttributes) {
                $tasksByIndex[$index] = WorkTask::query()->create([
                    'work_order_id' => $workOrder->id,
                    'created_by' => $actor->id,
                    'task_number' => 'CT-TK-'.Str::upper((string) Str::ulid()),
                    'title' => trim($taskAttributes['title']),
                    'instructions' => isset($taskAttributes['instructions']) ? trim($taskAttributes['instructions']) : null,
                    'status' => WorkTaskStatus::Queued,
                    'priority' => CivicReportPriority::from($taskAttributes['priority']),
                    'sequence' => $index + 1,
                    'due_at' => $taskAttributes['due_at'] ?? null,
                ]);
            }

            foreach (array_values($attributes['tasks']) as $index => $taskAttributes) {
                foreach ($taskAttributes['depends_on'] ?? [] as $dependencyIndex) {
                    $dependencyIndex = (int) $dependencyIndex;
                    $prerequisite = $tasksByIndex[$dependencyIndex] ?? null;

                    if ($prerequisite === null || $dependencyIndex === $index) {
                        throw ValidationException::withMessages([
                            "tasks.$index.depends_on" => 'Choose a different task in this work order as the prerequisite.',
                        ]);
                    }

                    TaskDependency::query()->create([
                        'work_task_id' => $tasksByIndex[$index]->id,
                        'depends_on_task_id' => $prerequisite->id,
                        'created_by' => $actor->id,
                    ]);
                }
            }

            $this->auditWriter->record(
                'work_order.created',
                $workOrder,
                ['civic_report_id' => $report->id, 'task_count' => count($tasksByIndex)],
                $actor,
            );

            return $workOrder->load('tasks');
        });
    }

    /** @param array<int, array{depends_on?: array<int, int|string>}> $tasks */
    private function assertDependencyGraphIsAcyclic(array $tasks): void
    {
        $graph = [];
        $taskCount = count($tasks);

        foreach (array_values($tasks) as $index => $task) {
            foreach ($task['depends_on'] ?? [] as $dependencyIndex) {
                $dependencyIndex = (int) $dependencyIndex;

                if ($dependencyIndex < 0 || $dependencyIndex >= $taskCount || $dependencyIndex === $index) {
                    throw ValidationException::withMessages([
                        "tasks.$index.depends_on" => 'Choose a different task in this work order as the prerequisite.',
                    ]);
                }

                $graph[$index][] = $dependencyIndex;
            }
        }

        $visiting = [];
        $visited = [];
        $visit = function (int $index) use (&$visit, &$graph, &$visiting, &$visited): bool {
            if (isset($visiting[$index])) {
                return false;
            }

            if (isset($visited[$index])) {
                return true;
            }

            $visiting[$index] = true;

            foreach ($graph[$index] ?? [] as $dependencyIndex) {
                if (! $visit($dependencyIndex)) {
                    return false;
                }
            }

            unset($visiting[$index]);
            $visited[$index] = true;

            return true;
        };

        foreach (array_keys(array_values($tasks)) as $index) {
            if (! $visit($index)) {
                throw ValidationException::withMessages(['tasks' => 'Task dependencies cannot contain a cycle.']);
            }
        }
    }

    private function authorizeCreate(CivicReport $report, User $actor): void
    {
        if ($actor->can(PermissionName::ReportsManageAll->value)) {
            return;
        }

        if (! $actor->can(PermissionName::WorkOrdersManageDepartment->value)
            || $report->routed_unit_id === null
            || ! UnitMembership::query()
                ->where('user_id', $actor->id)
                ->where('organization_unit_id', $report->routed_unit_id)
                ->where('membership_type', RoleName::Staff->value)
                ->where('status', 'active')
                ->exists()) {
            throw ValidationException::withMessages(['report' => 'You are not authorized to plan work for this report.']);
        }
    }
}
