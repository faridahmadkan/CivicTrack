<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\Report;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CitizenController extends Controller
{
    public function dashboard(Request $request)
    {
        $reports = Report::where('citizen_id', $request->user()->id);
        return view('citizen.dashboard', [
            'stats' => [
                'total' => (clone $reports)->count(),
                'pending' => (clone $reports)->where('status', 'pending_review')->count(),
                'approved' => (clone $reports)->whereIn('status', ['approved', 'assigned', 'in_progress'])->count(),
                'in_progress' => (clone $reports)->where('status', 'in_progress')->count(),
                'resolved' => (clone $reports)->where('status', 'resolved')->count(),
            ],
            'recentReports' => (clone $reports)->latest()->take(5)->get(),
            'notifications' => $request->user()->notifications()->latest()->take(5)->get(),
        ]);
    }

    public function index(Request $request)
    {
        $reports = Report::with('category')
            ->where('citizen_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('citizen.reports.index', compact('reports'));
    }

    public function create()
    {
        return view('citizen.reports.create', [
            'categories' => Category::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category_id' => ['required', 'exists:categories,id'],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'photos' => ['required', 'array', 'min:1', 'max:5'],
            'photos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $report = DB::transaction(function () use ($request, $data) {
            $report = Report::create([
                'reference_number' => 'CT-'.now()->format('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),
                'citizen_id' => $request->user()->id,
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'location' => $data['location'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'priority' => $data['priority'],
                'status' => 'pending_review',
            ]);

            ReportStatusHistory::create([
                'report_id' => $report->id,
                'updated_by' => $request->user()->id,
                'previous_status' => null,
                'new_status' => 'pending_review',
                'notes' => 'Report submitted.',
            ]);

            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('report-attachments', 'public');
                $report->attachments()->create([
                    'uploaded_by' => $request->user()->id,
                    'file_path' => $path,
                    'original_name' => $photo->getClientOriginalName(),
                    'file_type' => $photo->getClientMimeType(),
                ]);
            }

            return $report;
        });

        User::where('role', 'admin')->get()->each(
            fn (User $admin) => $admin->notify(new SystemNotification(
                'New report submitted: '.$report->title,
                route('admin.reports.show', $report)
            ))
        );

        return redirect()->route('citizen.reports.show', $report)
            ->with('status', 'Your report was submitted successfully.');
    }

    public function show(Request $request, Report $report)
    {
        $this->authorize('view', $report);
        $report->load(['category', 'attachments', 'statusHistories.updater', 'progressUpdates.attachments', 'feedback']);

        return view('citizen.reports.show', compact('report'));
    }

    public function cancel(Request $request, Report $report)
    {
        $this->authorize('cancel', $report);

        DB::transaction(function () use ($request, $report) {
            $previous = $report->status;
            $report->update(['status' => 'cancelled']);
            ReportStatusHistory::create([
                'report_id' => $report->id,
                'updated_by' => $request->user()->id,
                'previous_status' => $previous,
                'new_status' => 'cancelled',
                'notes' => 'Cancelled by the reporting citizen.',
            ]);
        });

        return back()->with('status', 'Report cancelled.');
    }

    public function feedback(Request $request, Report $report)
    {
        $this->authorize('view', $report);
        abort_unless($report->citizen_id === $request->user()->id, 403);
        abort_unless($report->status === 'resolved', 403);
        abort_if($report->feedback()->exists(), 422);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        Feedback::create([
            'report_id' => $report->id,
            'citizen_id' => $request->user()->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return back()->with('status', 'Thank you for your feedback.');
    }
}
