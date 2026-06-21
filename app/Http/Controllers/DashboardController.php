<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->isSystemAdmin()) {
            return redirect()->route('admin.stats.index');
        }

        $filter = $request->query('filter');
        $notificationsQuery = $user->appNotifications()->latest()->limit(8);

        if ($filter === 'unread') {
            $notificationsQuery->whereNull('read_at');
        } elseif ($filter !== null) {
            $notificationsQuery->where('type', $filter);
        }

        return Inertia::render('Dashboard', [
            'summary' => [
                'groups' => $user->groups()->count(),
                'pendingPayments' => $user->payments()->whereIn('status', ['pending', 'rejected'])->count(),
                'unreadNotifications' => $user->appNotifications()->whereNull('read_at')->count(),
            ],
            'recentGroups' => $user->groups()->latest('groups.created_at')->limit(5)->get(),
            'notifications' => $notificationsQuery->get(),
            'filter' => $filter,
        ]);
    }
}
