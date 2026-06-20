<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function index(Request $request): Response
    {
        $groups = $request->user()
            ->groups()
            ->withCount('members', 'expenses')
            ->latest('groups.created_at')
            ->get();

        return Inertia::render('groups/Index', [
            'groups' => $groups,
        ]);
    }

    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $group = DB::transaction(function () use ($request): Group {
            $group = Group::query()->create([
                ...$request->validated(),
                'creator_id' => $request->user()->id,
                'invite_token' => Str::random(32),
            ]);

            $group->members()->attach($request->user()->id, ['role' => 'admin']);

            return $group;
        });

        return redirect()->route('groups.show', $group);
    }

    public function show(Request $request, Group $group): Response
    {
        abort_unless($this->isMember($request, $group), 403);

        $group->load(['members:id,name,email', 'expenses.payer:id,name']);

        return Inertia::render('groups/Show', [
            'group' => $group,
            'isAdmin' => $this->isAdmin($request, $group),
            'inviteUrl' => route('groups.join', [$group, $group->invite_token]),
        ]);
    }

    public function join(Request $request, Group $group, string $token): RedirectResponse
    {
        abort_unless(hash_equals($group->invite_token, $token), 404);

        $group->members()->syncWithoutDetaching([
            $request->user()->id => ['role' => 'member'],
        ]);

        return redirect()->route('groups.show', $group);
    }

    private function isMember(Request $request, Group $group): bool
    {
        return $group->members()->whereKey($request->user()->id)->exists();
    }

    private function isAdmin(Request $request, Group $group): bool
    {
        return $group->members()
            ->whereKey($request->user()->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }
}
