<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupExpenseController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('groups', [GroupController::class, 'index'])->name('groups.index');
    Route::post('groups', [GroupController::class, 'store'])->name('groups.store');
    Route::post('groups/{group}/expenses', [GroupExpenseController::class, 'store'])->name('groups.expenses.store');
    Route::post('groups/{group}/join/{token}', [GroupController::class, 'join'])->name('groups.join');
    Route::get('groups/{group}', [GroupController::class, 'show'])->name('groups.show');
});

Route::middleware(['auth', 'active', 'system.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('users', fn () => response('Admin users'))->name('users.index');
        Route::get('stats', fn () => response('Admin stats'))->name('stats.index');
    });

require __DIR__.'/settings.php';
