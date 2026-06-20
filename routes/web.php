<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth', 'active', 'system.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('users', fn () => response('Admin users'))->name('users.index');
        Route::get('stats', fn () => response('Admin stats'))->name('stats.index');
    });

require __DIR__.'/settings.php';
