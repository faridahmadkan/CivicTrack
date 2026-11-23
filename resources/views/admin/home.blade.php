@extends('layouts.admin')

@section('title', 'Command center')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-forest-700">System overview</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">Command center</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Manage service organization, access settings, and workforce approvals from one accountable workspace.</p>
        </div>
        <p class="text-xs font-medium text-slate-500">Updated {{ now()->format('M j, Y · H:i') }}</p>
    </div>

    <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @can('accounts.manage')
            <a href="{{ route('admin.accounts.index', ['role' => App\Enums\RoleName::Citizen->value]) }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Citizen accounts</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($citizenAccounts) }}</p><span class="mt-1 block text-xs font-semibold text-forest-800">View citizen accounts</span></a>
            <a href="{{ route('admin.accounts.index', ['role' => App\Enums\RoleName::Staff->value, 'status' => App\Enums\AccountStatus::Active->value]) }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Active staff</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($staffAccounts) }}</p><span class="mt-1 block text-xs font-semibold text-forest-800">View active staff accounts</span></a>
            <a href="{{ route('admin.accounts.index', ['role' => App\Enums\RoleName::Worker->value, 'status' => App\Enums\AccountStatus::Active->value]) }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Active workers</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($workerAccounts) }}</p><span class="mt-1 block text-xs font-semibold text-forest-800">View active worker accounts</span></a>
        @endcan
        @can('viewAny', App\Models\WorkforceApplication::class)
            <a href="{{ route('admin.workforce-applications.index') }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Open applications</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($pendingApplications) }}</p><span class="mt-1 block text-xs font-semibold text-forest-800">Review staff and worker applications</span></a>
        @endcan
        @can('organization.manage')
            <a href="{{ route('admin.organization-units.index', ['status' => 'active']) }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Active organization units</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($activeOrganizationUnits) }}</p><span class="mt-1 block text-xs font-semibold text-forest-800">Manage active service units</span></a>
        @endcan
        @can('viewAny', App\Models\CivicReport::class)
            <a href="{{ route('staff.reports.index', ['preset' => 'all_open']) }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Open reports</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($openReports) }}</p><span class="mt-1 block text-xs font-semibold text-forest-800">Review service workflow</span></a>
            <a href="{{ route('staff.reports.index', ['preset' => 'overdue']) }}" class="group rounded-2xl border border-red-100 bg-white p-5 shadow-sm transition hover:border-red-300 hover:bg-red-50/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-800"><p class="text-xs font-semibold uppercase tracking-wide text-red-800">Overdue service targets</p><p class="mt-3 text-3xl font-semibold tracking-tight text-red-950">{{ number_format($overdueSlaReports) }}</p><span class="mt-1 block text-xs font-semibold text-red-800">Review overdue reports</span></a>
            @can('reports.view.department')
            <a href="{{ route('staff.reports.index', ['preset' => 'reopen_requests']) }}" class="group rounded-2xl border border-amber-100 bg-white p-5 shadow-sm transition hover:border-amber-300 hover:bg-amber-50/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-800"><p class="text-xs font-semibold uppercase tracking-wide text-amber-800">Reopen reviews</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($pendingReopenRequests) }}</p><span class="mt-1 block text-xs font-semibold text-forest-800">Review pending requests</span></a>
            @endcan
        @endcan
        @can('registrations.manage')
            <a href="{{ route('admin.registration-controls.index') }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:border-forest-200 hover:bg-forest-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Enabled registration paths</p><p class="mt-3 text-3xl font-semibold tracking-tight text-forest-950">{{ number_format($openRegistrationControls) }}</p><span class="mt-1 block text-xs font-semibold text-forest-800">Manage registration settings</span></a>
        @endcan
    </div>

    <section class="mt-8 grid gap-5 lg:grid-cols-3" aria-label="Administrative workspaces">
        @can('viewAny', App\Models\WorkforceApplication::class)
            <a href="{{ route('admin.workforce-applications.index') }}" class="group rounded-2xl border border-line bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-forest-200 hover:shadow-md">
                <span class="flex size-11 items-center justify-center rounded-xl bg-forest-50 text-forest-800"><svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M8 4h8l4 4v12H4V4h4Zm7 0v5h5M8 13h8m-8 4h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <h2 class="mt-5 text-base font-semibold text-slate-900">Workforce applications</h2><p class="mt-2 text-sm leading-6 text-slate-600">Review staff and field worker applications, request details, and approve eligible applicants into a scoped organization role.</p>
                <span class="mt-5 inline-flex text-sm font-semibold text-forest-800 group-hover:text-forest-950">Open application queue →</span>
            </a>
        @endcan
        @can('organization.manage')
            <a href="{{ route('admin.organization-units.index') }}" class="group rounded-2xl border border-line bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-forest-200 hover:shadow-md">
                <span class="flex size-11 items-center justify-center rounded-xl bg-forest-50 text-forest-800"><svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><rect x="4" y="4" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.6"/><rect x="14" y="4" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.6"/><rect x="9" y="14" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.6"/><path d="M7 10v2h10v-2m-5 2v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <h2 class="mt-5 text-base font-semibold text-slate-900">Organization structure</h2><p class="mt-2 text-sm leading-6 text-slate-600">Maintain departments and nested operating units used by staff and worker approval.</p>
                <span class="mt-5 inline-flex text-sm font-semibold text-forest-800 group-hover:text-forest-950">Manage organization units →</span>
            </a>
        @endcan
        @can('registrations.manage')
            <a href="{{ route('admin.registration-controls.index') }}" class="group rounded-2xl border border-line bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-forest-200 hover:shadow-md">
                <span class="flex size-11 items-center justify-center rounded-xl bg-forest-50 text-forest-800"><svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M5 7h14M5 12h9m-9 5h14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="17" cy="12" r="2" fill="currentColor"/></svg></span>
                <h2 class="mt-5 text-base font-semibold text-slate-900">Registration controls</h2><p class="mt-2 text-sm leading-6 text-slate-600">Open or schedule citizen, staff applicant, and worker applicant registration independently.</p>
                <span class="mt-5 inline-flex text-sm font-semibold text-forest-800 group-hover:text-forest-950">Review access settings →</span>
            </a>
        @endcan
        @can('sla.manage')
            <a href="{{ route('admin.sla-policies.index') }}" class="group rounded-2xl border border-line bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-forest-200 hover:shadow-md"><h2 class="text-base font-semibold text-slate-900">Service level targets</h2><p class="mt-2 text-sm leading-6 text-slate-600">Set category, unit, and priority response goals and review escalation frequency.</p><span class="mt-5 inline-flex text-sm font-semibold text-forest-800 group-hover:text-forest-950">Manage service targets →</span></a>
        @endcan
        @can('audit.view')
            <a href="{{ route('admin.audit-events.index') }}" class="group rounded-2xl border border-line bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-forest-200 hover:shadow-md"><h2 class="text-base font-semibold text-slate-900">Audit trail</h2><p class="mt-2 text-sm leading-6 text-slate-600">Search attributed application decisions, routing changes, service activity, and escalations.</p><span class="mt-5 inline-flex text-sm font-semibold text-forest-800 group-hover:text-forest-950">Review audit events →</span></a>
        @endcan
    </section>
@endsection
