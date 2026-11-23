<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageReportSlaPolicyRequest;
use App\Models\OrganizationUnit;
use App\Models\ReportSlaPolicy;
use App\Models\ServiceCategory;
use App\Services\AuditWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ReportSlaPolicyController extends Controller
{
    public function index(): View
    {
        return view('admin.sla-policies.index', [
            'policies' => ReportSlaPolicy::query()
                ->with(['category', 'organizationUnit', 'creator'])
                ->withCount('instances')
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
            'categories' => ServiceCategory::query()->orderBy('name')->get(),
            'organizationUnits' => OrganizationUnit::query()->orderBy('name')->get(),
        ]);
    }

    public function store(ManageReportSlaPolicyRequest $request, AuditWriter $auditWriter): RedirectResponse
    {
        $policy = DB::transaction(function () use ($request, $auditWriter): ReportSlaPolicy {
            $policy = ReportSlaPolicy::query()->create([
                ...$request->validated(),
                'created_by' => $request->user()->id,
                'version' => 1,
            ]);
            $auditWriter->record('report_sla.policy_created', $policy, [
                'name' => $policy->name,
                'version' => $policy->version,
                'is_active' => $policy->is_active,
            ], $request->user());

            return $policy;
        });

        return to_route('admin.sla-policies.index')->with('status', 'SLA policy created (version '.$policy->version.').');
    }

    public function update(
        ManageReportSlaPolicyRequest $request,
        ReportSlaPolicy $reportSlaPolicy,
        AuditWriter $auditWriter,
    ): RedirectResponse {
        $version = DB::transaction(function () use ($request, $reportSlaPolicy, $auditWriter): int {
            $policy = ReportSlaPolicy::query()->lockForUpdate()->findOrFail($reportSlaPolicy->id);
            $policy->fill($request->validated());
            $policy->version++;
            $policy->save();

            $auditWriter->record('report_sla.policy_updated', $policy, [
                'name' => $policy->name,
                'version' => $policy->version,
                'is_active' => $policy->is_active,
            ], $request->user());

            return $policy->version;
        });

        return to_route('admin.sla-policies.index')->with('status', 'SLA policy saved as version '.$version.'.');
    }
}
