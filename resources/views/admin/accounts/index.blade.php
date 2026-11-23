@extends('layouts.admin')

@section('title', 'Account provisioning')
@section('eyebrow', 'Identity and access')

@section('content')
    @php($hasActiveFilters = filled($filters['q'] ?? null) || filled($filters['role'] ?? null) || filled($filters['status'] ?? null))
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Server-managed accounts</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Account provisioning</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Create citizen, staff, or field-worker accounts even when public registration and workforce applications are closed. Staff and workers receive only the selected unit membership. @if (config('civictrack.require_email_verification')) New accounts must verify their email before opening an operational workspace.@else Email verification is disabled for this installation.@endif</p>
    </div>

    <section class="mt-6 flex flex-col gap-4 rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between" aria-labelledby="pending-approvals-heading">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-800">Workforce approval queue</p>
            <h2 id="pending-approvals-heading" class="mt-1 text-base font-semibold text-amber-950">{{ number_format($pendingApplicationsCount) }} application{{ $pendingApplicationsCount === 1 ? '' : 's' }} waiting for review</h2>
            <p class="mt-1 max-w-2xl text-sm leading-6 text-amber-900">Applicants appear here with Citizen access while review is pending. Approving an application grants its staff or field-worker role and requested unit.</p>
        </div>
        <a href="{{ route('admin.workforce-applications.index') }}" class="group inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-forest-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-forest-800 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Review applications <svg viewBox="0 0 20 20" class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" aria-hidden="true"><path d="M4.5 10h11m0 0L11 5.5m4.5 4.5L11 14.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" /></svg></a>
    </section>

    <div class="mt-7 grid gap-6 xl:grid-cols-[22rem_1fr]">
        <section class="h-fit rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-base font-semibold text-slate-900">Create an account</h2>
            <p class="mt-1 text-xs leading-5 text-slate-500">The user receives a password setup link. @if (config('civictrack.require_email_verification')) An email verification link is also sent.@else Email verification is disabled and the account is verified automatically.@endif No password is set or shown by this form.</p>
            <form method="POST" action="{{ route('admin.accounts.store') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="account-name" class="mb-1.5 block text-xs font-semibold text-slate-700">Full name</label>
                    <input id="account-name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" class="w-full rounded-lg border border-line px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100">
                    @error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="account-email" class="mb-1.5 block text-xs font-semibold text-slate-700">Email address</label>
                    <input id="account-email" type="email" name="email" value="{{ old('email') }}" required maxlength="254" autocomplete="email" class="w-full rounded-lg border border-line px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100">
                    @error('email')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="account-role" class="mb-1.5 block text-xs font-semibold text-slate-700">Account context</label>
                    <select id="account-role" name="role" required class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100">
                        <option value="citizen" @selected(old('role', 'citizen') === 'citizen')>Registered citizen</option>
                        <option value="staff" @selected(old('role') === 'staff')>Staff</option>
                        <option value="worker" @selected(old('role') === 'worker')>Field worker</option>
                    </select>
                    @error('role')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="account-unit" class="mb-1.5 block text-xs font-semibold text-slate-700">Organization unit <span class="font-normal text-slate-500">(required for staff and workers)</span></label>
                    <select id="account-unit" name="organization_unit_id" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100">
                        <option value="">Choose an active unit</option>
                        @foreach ($organizationUnits as $organizationUnit)
                            <option value="{{ $organizationUnit->id }}" @selected((string) old('organization_unit_id') === (string) $organizationUnit->id)>{{ $organizationUnit->name }}</option>
                        @endforeach
                    </select>
                    @error('organization_unit_id')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="account-position" class="mb-1.5 block text-xs font-semibold text-slate-700">Position title <span class="font-normal text-slate-500">(required for staff and workers)</span></label>
                    <input id="account-position" name="position_title" value="{{ old('position_title') }}" maxlength="120" class="w-full rounded-lg border border-line px-3 py-2.5 text-sm focus:border-forest-700 focus:ring-4 focus:ring-forest-100">
                    @error('position_title')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="w-full rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-forest-800">Create account and send setup links</button>
            </form>
        </section>

        <section>
            <div class="mb-3 flex items-center justify-between gap-4"><h2 class="text-base font-semibold text-slate-900">Accounts</h2><span class="text-xs text-slate-500">{{ number_format($accounts->total()) }} matching</span></div>
            <form method="GET" action="{{ route('admin.accounts.index') }}" class="mb-4 grid gap-3 rounded-2xl border border-line bg-white p-4 sm:grid-cols-[minmax(12rem,1fr)_10rem_10rem_auto_auto] sm:items-end">
                <label class="text-xs font-semibold text-slate-600">Search accounts
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" class="mt-1.5 block w-full rounded-lg border border-line px-3 py-2.5 text-sm font-normal text-slate-800" placeholder="Name or email">
                </label>
                <label class="text-xs font-semibold text-slate-600">Role
                    <select name="role" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="">All roles</option>@foreach (App\Enums\RoleName::cases() as $role)<option value="{{ $role->value }}" @selected(($filters['role'] ?? '') === $role->value)>{{ $role->label() }}</option>@endforeach</select>
                </label>
                <label class="text-xs font-semibold text-slate-600">Account status
                    <select name="status" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm font-normal text-slate-800"><option value="">All statuses</option>@foreach (App\Enums\AccountStatus::cases() as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ str($status->value)->headline() }}</option>@endforeach</select>
                </label>
                <button class="rounded-lg bg-forest-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-800">Search</button>
                <a href="{{ route('admin.accounts.index') }}" class="rounded-lg border border-line bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-sand-50">Clear</a>
            </form>
            <div class="overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
                @forelse ($accounts as $account)
                    <article class="border-b border-line p-4 last:border-b-0 sm:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div><h3 class="text-sm font-semibold text-slate-900">{{ $account->name }}</h3><p class="mt-1 text-xs text-slate-600">{{ $account->email }}</p></div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($account->roles as $role)
                                    <span class="rounded-full bg-forest-50 px-2.5 py-1 text-[0.68rem] font-semibold text-forest-800">{{ str($role->name)->headline() }}</span>
                                @endforeach
                                <x-status-badge :status="$account->account_status" />
                            </div>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                            <span>{{ $account->hasVerifiedEmail() ? 'Email verified' : 'Email verification pending' }}</span>
                            @foreach ($account->unitMemberships as $membership)
                                <span>{{ str($membership->membership_type->value)->headline() }} Â· {{ $membership->organizationUnit->name }} Â· {{ str($membership->status->value)->headline() }}</span>
                            @endforeach
                        </div>
                        @foreach ($account->workforceApplications as $application)
                            <div class="mt-4 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50/70 p-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-amber-950">Pending {{ ucfirst($application->application_type->value) }} application</p>
                                    <p class="mt-1 text-xs text-amber-900">{{ $application->application_number }} · {{ $application->status->label() }} · {{ $application->requestedUnit?->name ?? 'Unit unavailable' }}</p>
                                </div>
                                <a href="{{ route('admin.workforce-applications.show', $application) }}" class="group inline-flex w-fit items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-semibold text-forest-900 shadow-sm ring-1 ring-amber-200 transition hover:bg-forest-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700">Review application <svg viewBox="0 0 16 16" class="size-3.5 transition-transform group-hover:translate-x-0.5" fill="none" aria-hidden="true"><path d="M3 8h10m0 0L9 4m4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg></a>
                            </div>
                        @endforeach
                    </article>
                @empty
                    @if ($hasActiveFilters)
                        <x-empty-state title="No accounts match these filters" description="Try another name, role, or account status, or clear the filters to see all accounts." :href="route('admin.accounts.index')" action="Clear filters" />
                    @else
                        <x-empty-state title="No accounts found" description="Created accounts will appear here." />
                    @endif
                @endforelse
            </div>
            <div class="mt-4">{{ $accounts->links() }}</div>
        </section>
    </div>
@endsection
