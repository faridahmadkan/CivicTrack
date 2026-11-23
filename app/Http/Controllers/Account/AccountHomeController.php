<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Enums\RoleName;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccountHomeController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasRole(RoleName::Administrator->value)) {
            return to_route('admin.home');
        }

        if ($request->user()->hasRole(RoleName::Staff->value)) {
            return to_route('staff.reports.index');
        }

        if ($request->user()->hasRole(RoleName::Worker->value)) {
            return to_route('worker.home');
        }

        if ($request->user()->hasRole(RoleName::Citizen->value)) {
            return to_route('citizen.home');
        }

        return view('account.home', [
            'user' => $request->user(),
            'roles' => $request->user()->getRoleNames(),
        ]);
    }
}
