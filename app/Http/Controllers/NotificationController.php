<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->query('filter');
        $query = $request->user()->appNotifications()->latest();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter !== null) {
            $query->where('type', $filter);
        }

        $notifications = $query->paginate(15)->withQueryString();

        return Inertia::render('Dashboard', [
            'notifications' => $notifications,
            'filter' => $filter,
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->appNotifications()->whereNull('read_at')->count();

        return response()->json(['count' => $count]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->appNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back();
    }

    public function read(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['read_at' => now()]);

        return back();
    }
}
