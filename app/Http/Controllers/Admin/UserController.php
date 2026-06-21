<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserStatusRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Users', [
            'users' => User::query()
                ->select(['id', 'name', 'email', 'role', 'is_active', 'created_at'])
                ->latest()
                ->paginate(20),
            'summary' => [
                'total' => User::query()->count(),
                'active' => User::query()->where('is_active', true)->count(),
                'inactive' => User::query()->where('is_active', false)->count(),
                'systemAdmins' => User::query()->where('role', 'system_admin')->count(),
            ],
        ]);
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'You cannot change your own active status.');

        $user->update([
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.index');
    }
}
