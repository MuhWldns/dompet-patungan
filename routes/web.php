<?php

use App\Http\Controllers\Admin\GroupController as AdminGroupController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupExpenseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettlementController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('groups', [GroupController::class, 'index'])->name('groups.index');
    Route::post('groups', [GroupController::class, 'store'])->name('groups.store');
    Route::post('groups/{group}/expenses', [GroupExpenseController::class, 'store'])->name('groups.expenses.store');
    Route::get('groups/{group}/join/{token}', [GroupController::class, 'joinPreview'])->name('groups.join.show');
    Route::post('groups/{group}/join/{token}', [GroupController::class, 'join'])->name('groups.join');
    Route::get('groups/{group}', [GroupController::class, 'show'])->name('groups.show');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/{payment}/pay', [PaymentController::class, 'pay'])->name('payments.pay');
    Route::patch('payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::patch('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    Route::get('settlements/{group}', [SettlementController::class, 'show'])->name('settlements.show');
    Route::post('settlements/generate/{group}', [SettlementController::class, 'generate'])->name('settlements.generate');

    Route::patch('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
});

Route::middleware(['auth', 'active', 'system.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
        Route::get('groups', [AdminGroupController::class, 'index'])->name('groups.index');
        Route::get('stats', StatsController::class)->name('stats.index');
    });

require __DIR__.'/settings.php';
