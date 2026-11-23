<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\ManageRegistrationControlsRequest;
use App\Models\RegistrationControl;
use App\Services\AuditWriter;
use App\Services\RegistrationControlService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class RegistrationControlController extends Controller
{
    public function index(): View
    {
        $this->authorize('registrations.manage');

        return view('admin.registration-controls.index', [
            'controls' => RegistrationControl::query()->orderBy('context')->get()->keyBy('context'),
            'contexts' => [RoleName::Citizen, RoleName::Staff, RoleName::Worker],
        ]);
    }

    public function update(
        ManageRegistrationControlsRequest $request,
        string $context,
        RegistrationControlService $service,
        AuditWriter $auditWriter,
    ): RedirectResponse {
        $role = RoleName::tryFrom($context);

        abort_unless(in_array($role, [RoleName::Citizen, RoleName::Staff, RoleName::Worker], true), 404);

        $attributes = $request->validated();

        abort_unless($attributes['context'] === $context, 422);

        $service->configure(
            $role,
            filter_var($attributes['enabled'], FILTER_VALIDATE_BOOL),
            isset($attributes['opens_at']) ? Carbon::parse($attributes['opens_at']) : null,
            isset($attributes['closes_at']) ? Carbon::parse($attributes['closes_at']) : null,
            $request->user(),
            $auditWriter,
        );

        return to_route('admin.registration-controls.index')->with('status', 'Registration settings updated.');
    }
}
