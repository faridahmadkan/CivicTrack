<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WorkforceApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateAccountRequest;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Models\WorkforceApplication;
use App\Services\AdminAccountProvisioningService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Throwable;

class AccountController extends Controller
{
    public function index(): View
    {
        $pendingStatuses = [
            WorkforceApplicationStatus::Submitted->value,
            WorkforceApplicationStatus::UnderReview->value,
            WorkforceApplicationStatus::InformationRequested->value,
        ];

        return view('admin.accounts.index', [
            'accounts' => User::query()
                ->with([
                    'roles',
                    'unitMemberships.organizationUnit',
                    'workforceApplications' => function (HasMany $query) use ($pendingStatuses): void {
                        $query->whereIn('status', $pendingStatuses)
                            ->with('requestedUnit')
                            ->orderByDesc('submitted_at');
                    },
                ])
                ->orderBy('name')
                ->paginate(25),
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
