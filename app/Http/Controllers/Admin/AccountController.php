<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateAccountRequest;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Services\AdminAccountProvisioningService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Throwable;

class AccountController extends Controller
{
    public function index(): View
    {
        return view('admin.accounts.index', [
            'accounts' => User::query()
                ->with(['roles', 'unitMemberships.organizationUnit'])
                ->orderBy('name')
                ->paginate(25),
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
            $user->sendEmailVerificationNotification();
            $emailsSent = Password::sendResetLink(['email' => $user->email]) === Password::RESET_LINK_SENT;
        } catch (Throwable $exception) {
            report($exception);
        }

        $message = $emailsSent
            ? 'Account created. Password setup and email verification instructions were sent.'
            : 'Account created. Email delivery failed; configure mail and use the password reset flow before the user can sign in.';

        return to_route('admin.accounts.index')->with('status', $message);
    }
}
