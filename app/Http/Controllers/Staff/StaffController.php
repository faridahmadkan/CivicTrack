<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\ProgressAttachment;
use App\Models\ProgressUpdate;
use App\Models\Report;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    public function dashboard(Request $request)
    {
        $assignments = Assignment::where('staff_id', $request->user()->id);
        $assignedIds = (clone $assignments)->pluck('report_id');
        return view('staff.dashboard', [
            'stats' => [
                'total' => (clone $assignments)->count(),
                'pending' => (clone $assignments)->where('status', 'pending')->count(),
                'progress' => Report::whereIn('id', $assignedIds)->where('status', 'in_progress')->count(),
                'resolved' => Report::whereIn('id', $assignedIds)->where('status', 'resolved')->count(),
            ],
            'recentAssignments' => Assignment::with('report.category')->where('staff_id', $request->user()->id)->latest()->take(8)->get(),
        ]);
    }

    public function index(Request $request)
    {
        $assignments = Assignment::with('report.category')
            ->where('staff_id', $request->user()->id)
            ->latest()->paginate(15);

        return view('staff.assignments.index', compact('assignments'));
    }

    public function show(Request $request, Assignment $assignment)
    {
        $this->authorize('view', $assignment);
        $assignment->load(['report.attachments', 'report.statusHistories.updater', 'report.progressUpdates.attachments', 'staff']);

        return view('staff.assignments.show', compact('assignment'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);
        $data = $request->validate([
            'description' => ['required', 'string', 'max:5000'],
            'is_internal' => ['nullable', 'boolean'],
            'evidence' => ['nullable', 'array', 'max:5'],
            'evidence.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $report = $assignment->report;
        if ($report->status === 'assigned') {
            DB::transaction(function () use ($request, $assignment, $report, $data) {
                $previous = $report->status;
                $report->update(['status' => 'in_progress']);
                ReportStatusHistory::create([
                    'report_id' => $report->id,
                    'updated_by' => $request->user()->id,
                    'previous_status' => $previous,
                    'new_status' => 'in_progress',
                    'notes' => 'Investigation started.',
                ]);
            });
        }

        $progress = DB::transaction(function () use ($request, $report, $data) {
            $progress = ProgressUpdate::create([
                'report_id' => $report->id,
                'staff_id' => $request->user()->id,
                'description' => $data['description'],
                'is_internal' => $request->boolean('is_internal'),
            ]);
            foreach ($request->file('evidence', []) as $file) {
                $path = $file->store('progress-attachments', 'public');
                $progress->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
            $assignment = $progress->report->assignments()->where('staff_id', $request->user()->id)->first();
            if ($assignment) {
                $assignment->update(['status' => 'active']);
            }
            return $progress;
        });

        if (! (bool) ($data['is_internal'] ?? false)) {
            $report->citizen->notify(new SystemNotification('A progress update was added to '.$report->reference_number.'.', route('citizen.reports.show', $report)));
        }

        return back()->with('status', 'Progress update added.');
    }

    public function resolve(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);
        $data = $request->validate([
            'resolution_details' => ['required', 'string', 'max:5000'],
            'completion_photos' => ['required', 'array', 'min:1', 'max:5'],
            'completion_photos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $report = $assignment->report;
        abort_unless(in_array($report->status, ['assigned', 'in_progress'], true), 403);

        DB::transaction(function () use ($request, $assignment, $report, $data) {
            $progress = ProgressUpdate::create([
                'report_id' => $report->id,
                'staff_id' => $request->user()->id,
                'description' => $data['resolution_details'],
                'is_internal' => false,
            ]);
            foreach ($request->file('completion_photos') as $file) {
                $progress->attachments()->create([
                    'file_path' => $file->store('progress-attachments', 'public'),
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }

            $previous = $report->status;
            $report->update(['status' => 'resolved']);
            $assignment->update(['status' => 'completed']);
            ReportStatusHistory::create([
                'report_id' => $report->id,
                'updated_by' => $request->user()->id,
                'previous_status' => $previous,
                'new_status' => 'resolved',
                'notes' => 'Resolved by assigned staff; awaiting citizen feedback.',
            ]);
        });

        $report->citizen->notify(new SystemNotification('Your report '.$report->reference_number.' has been resolved.', route('citizen.reports.show', $report)));
        User::where('role', 'admin')->get()->each(
            fn (User $admin) => $admin->notify(new SystemNotification('Report '.$report->reference_number.' was marked resolved.', route('admin.reports.show', $report)))
        );

        return back()->with('status', 'Issue marked resolved.');
    }
}
