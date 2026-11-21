<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Models\Category;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Citizen\CitizenController;
use App\Http\Controllers\Shared\NotificationController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Shared\ProfileController;

Route::get('/', function () {
    return view('public.home', [
        'categories' => Category::where('is_active', true)->get(),
        'stats' => [
            'reports' => Report::count(),
            'resolved' => Report::where('status', 'resolved')->count(),
            'citizens' => \App\Models\User::where('role', 'citizen')->count(),
        ],
    ]);
})->name('home');

Route::view('/about', 'public.about')->name('about');
Route::view('/how-it-works', 'public.how-it-works')->name('how-it-works');
Route::view('/contact', 'public.contact')->name('contact');
Route::post('/contact', fn () => back()->with('status', 'Thank you. Your message has been received.'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return match (Auth::user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'staff' => redirect()->route('staff.dashboard'),
            default => redirect()->route('citizen.dashboard'),
        };
    })->name('dashboard');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'role:citizen'])->prefix('citizen')->name('citizen.')->group(function () {
    Route::get('/dashboard', [CitizenController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports', [CitizenController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [CitizenController::class, 'create'])->name('reports.create');
    Route::post('/reports', [CitizenController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}', [CitizenController::class, 'show'])->name('reports.show');
    Route::post('/reports/{report}/cancel', [CitizenController::class, 'cancel'])->name('reports.cancel');
    Route::post('/reports/{report}/feedback', [CitizenController::class, 'feedback'])->name('reports.feedback');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create-staff', [AdminController::class, 'createStaff'])->name('users.create-staff');
    Route::post('/users/create-staff', [AdminController::class, 'storeStaff'])->name('users.store-staff');
    Route::post('/users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
    Route::get('/departments', [AdminController::class, 'departments'])->name('departments');
    Route::post('/departments', [AdminController::class, 'storeDepartment'])->name('departments.store');
    Route::put('/departments/{department}', [AdminController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{department}', [AdminController::class, 'destroyDepartment'])->name('departments.destroy');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports.index');
    Route::get('/reports/{report}', [AdminController::class, 'showReport'])->name('reports.show');
    Route::post('/reports/{report}/approve', [AdminController::class, 'approve'])->name('reports.approve');
    Route::post('/reports/{report}/reject', [AdminController::class, 'reject'])->name('reports.reject');
    Route::post('/reports/{report}/assign', [AdminController::class, 'assign'])->name('reports.assign');
    Route::post('/reports/{report}/reopen', [AdminController::class, 'reopen'])->name('reports.reopen');
    Route::get('/feedback', [AdminController::class, 'feedback'])->name('feedback');
});

Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
    Route::get('/assignments', [StaffController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{assignment}', [StaffController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{assignment}/progress', [StaffController::class, 'update'])->name('assignments.progress');
    Route::post('/assignments/{assignment}/resolve', [StaffController::class, 'resolve'])->name('assignments.resolve');
});
