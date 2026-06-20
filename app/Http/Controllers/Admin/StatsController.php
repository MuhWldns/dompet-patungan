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
                'groups' => Group::query()->count(),
                'expenses' => Expense::query()->count(),
                'expenseTotal' => Expense::query()->sum('amount'),
                'confirmedPaymentTotal' => Payment::query()->where('status', 'confirmed')->sum('amount'),
            ],
        ]);
    }
}
