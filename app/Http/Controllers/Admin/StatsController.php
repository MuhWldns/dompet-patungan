<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Group;
use App\Models\Payment;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class StatsController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/Stats', [
            'stats' => [
                'users' => User::query()->count(),
                'activeUsers' => User::query()->where('is_active', true)->count(),
                'inactiveUsers' => User::query()->where('is_active', false)->count(),
                'systemAdmins' => User::query()->where('role', 'system_admin')->count(),
                'groups' => Group::query()->count(),
                'activeGroups' => Group::query()->where('status', 'active')->count(),
                'settledGroups' => Group::query()->where('status', 'settled')->count(),
                'closedGroups' => Group::query()->where('status', 'closed')->count(),
                'expenses' => Expense::query()->count(),
                'expenseTotal' => Expense::query()->sum('amount'),
                'pendingPayments' => Payment::query()->where('status', 'pending')->count(),
                'submittedPayments' => Payment::query()->where('status', 'submitted')->count(),
                'rejectedPayments' => Payment::query()->where('status', 'rejected')->count(),
                'confirmedPaymentTotal' => Payment::query()->where('status', 'confirmed')->sum('amount'),
            ],
        ]);
    }
}
