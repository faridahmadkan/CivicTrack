<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Report;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\Models\Assignment;
use App\Models\Feedback;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'stats' => [
                'citizens' => User::where('role', 'citizen')->count(),
                'staff' => User::where('role', 'staff')->count(),
                'reports' => Report::count(),
                'pending' => Report::where('status', 'pending_review')->count(),
                'assigned' => Report::whereIn('status', ['approved', 'assigned', 'in_progress'])->count(),
                'resolved' => Report::where('status', 'resolved')->count(),
                'rejected' => Report::where('status', 'rejected')->count(),
            ],
            'recentReports' => Report::with('citizen', 'category')->latest()->take(8)->get(),
            'categories' => Category::withCount('reports')->get(),
            'monthlyReports' => Report::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total')
                ->groupBy('month')->orderBy('month')->get(),
        ]);
    }

    public function users(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn ($query) => $query->where('name', 'like', '%'.$request->search.'%'))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function toggleUser(User $user)
    {
        abort_if($user->isAdmin() && $user->id === auth()->id(), 403);
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', 'User account status updated.');
    }

    public function createStaff()
    {
        return view('admin.users-create-staff');
    }

    public function storeStaff(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'role' => 'staff',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users')->with('status', 'Staff account created.');
    }

    public function categories()
    {
        return view('admin.categories', ['categories' => Category::latest()->paginate(15)]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        Category::create([...$data, 'slug' => Str::slug($data['name']), 'is_active' => true]);

        return back()->with('status', 'Category created.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,'.$category->id],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $category->update([...$data, 'slug' => Str::slug($data['name']), 'is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'Category updated.');
    }

    public function destroyCategory(Category $category)
    {
        abort_if($category->reports()->exists(), 422, 'A category with reports cannot be deleted.');
        $category->delete();

        return back()->with('status', 'Category deleted.');
    }

    public function departments()
    {
        return view('admin.departments', [
            'departments' => Department::latest()->paginate(15),
        ]);
    }

    public function storeDepartment(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'description' => ['nullable', 'string', 'max:2000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ]);
        Department::create($data);

        return back()->with('status', 'Department created.');
    }

    public function reports(Request $request)
    {
        $reports = Report::with(['citizen', 'category', 'assignments.staff'])
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('reference_number', 'like', '%'.$request->search.'%');
            }))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'categories' => Category::where('is_active', true)->get(),
        ]);
    }

    public function showReport(Report $report)
    {
        $report->load(['citizen', 'category', 'department', 'attachments', 'assignments.staff', 'statusHistories.updater', 'progressUpdates.attachments', 'feedback']);
        return view('admin.reports.show', [
            'report' => $report,
            'staff' => User::where('role', 'staff')->where('is_active', true)->get(),
            'departments' => Department::get(),
        ]);
    }

    public function approve(Request $request, Report $report)
    {
        abort_unless($report->status === 'pending_review', 403);
        DB::transaction(function () use ($request, $report) {
            $this->changeStatus($request->user(), $report, 'approved', 'Report approved for assignment.');
            $report->citizen->notify(new SystemNotification('Your report '.$report->reference_number.' was approved.', route('citizen.reports.show', $report)));
        });
        return back()->with('status', 'Report approved.');
    }

    public function reject(Request $request, Report $report)
    {
        abort_unless(in_array($report->status, ['pending_review', 'approved'], true), 403);
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:2000']]);
        DB::transaction(function () use ($request, $report, $data) {
            $report->update(['rejection_reason' => $data['rejection_reason']]);
            $this->changeStatus($request->user(), $report, 'rejected', $data['rejection_reason']);
            $report->citizen->notify(new SystemNotification('Your report '.$report->reference_number.' was rejected.', route('citizen.reports.show', $report)));
        });
        return back()->with('status', 'Report rejected.');
    }

    public function assign(Request $request, Report $report)
    {
        abort_unless($report->status === 'approved', 403);
        $data = $request->validate([
            'staff_id' => ['required', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $staff = User::where('role', 'staff')->where('is_active', true)->findOrFail($data['staff_id']);

        DB::transaction(function () use ($request, $report, $staff, $data) {
            Assignment::updateOrCreate(
                ['report_id' => $report->id],
                [
                    'staff_id' => $staff->id,
                    'assigned_by' => $request->user()->id,
                    'assigned_at' => now(),
                    'notes' => $data['notes'] ?? null,
                    'status' => 'pending',
                ]
            );
            $report->update(['status' => 'assigned', 'department_id' => $data['department_id'] ?? $report->department_id]);
            ReportStatusHistory::create([
                'report_id' => $report->id,
                'updated_by' => $request->user()->id,
                'previous_status' => 'approved',
                'new_status' => 'assigned',
                'notes' => 'Assigned to '.$staff->name.'. '.($data['notes'] ?? ''),
            ]);
            $staff->notify(new SystemNotification('A report was assigned to you: '.$report->title, route('staff.assignments.show', $report->assignments()->where('staff_id', $staff->id)->first())));
            $report->citizen->notify(new SystemNotification('Your report '.$report->reference_number.' was assigned to a staff member.', route('citizen.reports.show', $report)));
        });

        return back()->with('status', 'Report assigned.');
    }

    public function reopen(Request $request, Report $report)
    {
        abort_unless($report->status === 'resolved', 403);
        $data = $request->validate(['notes' => ['nullable', 'string', 'max:2000']]);

        DB::transaction(function () use ($request, $report, $data) {
            $this->changeStatus($request->user(), $report, 'approved', $data['notes'] ?? 'Report reopened for further action.');
            $report->citizen->notify(new SystemNotification('Your report '.$report->reference_number.' has been reopened.', route('citizen.reports.show', $report)));
            User::where('role', 'admin')->get()->each(
                fn (User $admin) => $admin->notify(new SystemNotification('Report '.$report->reference_number.' was reopened.', route('admin.reports.show', $report)))
            );
        });

        return back()->with('status', 'Report reopened.');
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name,'.$department->id],
            'description' => ['nullable', 'string', 'max:2000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ]);
        $department->update($data);

        return back()->with('status', 'Department updated.');
    }

    public function destroyDepartment(Department $department)
    {
        abort_if($department->reports()->exists(), 422, 'A department with reports cannot be deleted.');
        $department->delete();

        return back()->with('status', 'Department deleted.');
    }

    public function feedback()
    {
        return view('admin.feedback', ['feedback' => Feedback::with(['report', 'citizen'])->latest()->paginate(15)]);
    }

    private function changeStatus(User $user, Report $report, string $newStatus, ?string $notes = null): void
    {
        $previous = $report->status;
        $report->update(['status' => $newStatus]);
        ReportStatusHistory::create([
            'report_id' => $report->id,
            'updated_by' => $user->id,
            'previous_status' => $previous,
            'new_status' => $newStatus,
            'notes' => $notes,
        ]);
    }
}
