@extends('layouts.admin')

@section('title', 'Account provisioning')
@section('eyebrow', 'Identity and access')

@section('content')
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">Server-managed accounts</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Account provisioning</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Create citizen, staff, or field-worker accounts even when public registration and workforce applications are closed. Staff and workers receive only the selected unit membership. New accounts must verify their email before opening an operational workspace.</p>
    </div>

    <div class="mt-7 grid gap-6 xl:grid-cols-[22rem_1fr]">
        <section class="h-fit rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-base font-semibold text-slate-900">Create an account</h2>
            <p class="mt-1 text-xs leading-5 text-slate-500">The user receives a password setup link and an email verification link. No password is set or shown by this form.</p>
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
            <div class="mb-3 flex items-center justify-between gap-4"><h2 class="text-base font-semibold text-slate-900">Accounts</h2><span class="text-xs text-slate-500">{{ $accounts->total() }} total</span></div>
            <div class="overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
                @forelse ($accounts as $account)
                    <article class="border-b border-line p-4 last:border-b-0 sm:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div><h3 class="text-sm font-semibold text-slate-900">{{ $account->name }}</h3><p class="mt-1 text-xs text-slate-600">{{ $account->email }}</p></div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($account->roles as $role)
                                    <span class="rounded-full bg-forest-50 px-2.5 py-1 text-[0.68rem] font-semibold text-forest-800">{{ str($role->name)->headline() }}</span>
                                @endforeach
                                <span class="rounded-full bg-sand-100 px-2.5 py-1 text-[0.68rem] font-semibold text-slate-700">{{ str($account->account_status->value)->headline() }}</span>
                            </div>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                            <span>{{ $account->hasVerifiedEmail() ? 'Email verified' : 'Email verification pending' }}</span>
                            @foreach ($account->unitMemberships as $membership)
                                <span>{{ str($membership->membership_type->value)->headline() }} Â· {{ $membership->organizationUnit->name }} Â· {{ str($membership->status->value)->headline() }}</span>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="px-6 py-12 text-center"><p class="text-sm font-semibold text-slate-800">No accounts found</p><p class="mt-1 text-sm text-slate-500">Created accounts will appear here.</p></div>
                @endforelse
            </div>
            <div class="mt-4">{{ $accounts->links() }}</div>
        </section>
    </div>
@endsection
