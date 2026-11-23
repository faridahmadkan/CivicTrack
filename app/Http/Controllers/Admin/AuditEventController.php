<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditEventFilterRequest;
use App\Models\AuditEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class AuditEventController extends Controller
{
    public function index(AuditEventFilterRequest $request): View
    {
        $filters = $request->validated();
        $query = AuditEvent::query()->with('actor')->orderByDesc('occurred_at')->orderByDesc('id');

        if (filled($filters['event_key'] ?? null)) {
            $query->where('event_key', 'like', '%'.trim($filters['event_key']).'%');
        }

        if (filled($filters['actor_id'] ?? null)) {
            $query->where('actor_id', $filters['actor_id']);
        }

        if (filled($filters['from'] ?? null)) {
            $query->where('occurred_at', '>=', Carbon::parse($filters['from'])->startOfDay());
        }

        if (filled($filters['to'] ?? null)) {
            $query->where('occurred_at', '<=', Carbon::parse($filters['to'])->endOfDay());
        }

        return view('admin.audit-events.index', [
            'events' => $query->paginate(50)->withQueryString(),
            'filters' => $filters,
            'actors' => \App\Models\User::query()
                ->whereHas('auditEvents')
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }
}
