<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Enums\WorkforceApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateAccountRequest;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Models\WorkforceApplication;
use App\Services\AdminAccountProvisioningService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Throwable;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', Rule::enum(RoleName::class)],
            'status' => ['nullable', Rule::enum(AccountStatus::class)],
        ]);
        $pendingStatuses = [
            WorkforceApplicationStatus::Submitted->value,
            WorkforceApplicationStatus::UnderReview->value,
            WorkforceApplicationStatus::InformationRequested->value,
        ];

        $accounts = User::query()
            ->with([
                'roles',
                'unitMemberships.organizationUnit',
                'workforceApplications' => function (HasMany $query) use ($pendingStatuses): void {
                    $query->whereIn('status', $pendingStatuses)
                        ->with('requestedUnit')
                        ->orderByDesc('submitted_at');
                },
            ])
            ->when(filled($filters['q'] ?? null), function (Builder $query) use ($filters): void {
                $search = '%'.trim($filters['q']).'%';
                $query->where(static function (Builder $accounts) use ($search): void {
                    $accounts->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when(filled($filters['role'] ?? null), fn (Builder $query) => $query->whereHas(
                'roles',
                static fn (Builder $roles) => $roles->where('name', $filters['role']),
            ))
            ->when(filled($filters['status'] ?? null), fn (Builder $query) => $query->where('account_status', $filters['status']))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.accounts.index', [
            'accounts' => $accounts,
            'filters' => $filters,
            'pendingApplicationsCount' => WorkforceApplication::query()
                ->whereIn('status', $pendingStatuses)
                ->count(),
            'organizationUnits' => OrganizationUnit::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(
        CreateAccountRequest $request,
        AdminAccountProvisioningService $service,
    ): RedirectResponse {
        $user = $service->create($request->user(), $request->validated());
        $emailsSent = false;

        try {
            if (config('civictrack.require_email_verification')) {
                $user->sendEmailVerificationNotification();
            }
            $emailsSent = Password::sendResetLink(['email' => $user->email]) === Password::RESET_LINK_SENT;
        } catch (Throwable $exception) {
            report($exception);
        }

        $message = $emailsSent
            ? (config('civictrack.require_email_verification')
                ? 'Account created. Password setup and email verification instructions were sent.'
                : 'Account created. A password setup link was sent; email verification is disabled.')
            : 'Account created. Email delivery failed; configure mail and use the password reset flow before the user can sign in.';

        return to_route('admin.accounts.index')->with('status', $message);
    }
}
