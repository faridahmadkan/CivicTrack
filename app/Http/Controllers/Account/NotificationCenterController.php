<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationCenterController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['read', 'unread'])],
        ]);
        $query = $request->user()->notifications()->orderByDesc('created_at');

        if (filled($filters['q'] ?? null)) {
            $search = '%'.trim($filters['q']).'%';
            $query->where(static function (Builder $notifications) use ($search): void {
                $notifications->where('type', 'like', $search)
                    ->orWhere('data', 'like', $search);
            });
        }

        if (($filters['status'] ?? null) === 'read') {
            $query->whereNotNull('read_at');
        } elseif (($filters['status'] ?? null) === 'unread') {
            $query->whereNull('read_at');
        }

        return view('account.notifications.index', [
            'notifications' => $query->paginate(30)->withQueryString(),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
            'filters' => $filters,
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $record->markAsRead();

        return to_route('account.notifications.index')->with('status', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return to_route('account.notifications.index')->with('status', 'All notifications marked as read.');
    }
}
