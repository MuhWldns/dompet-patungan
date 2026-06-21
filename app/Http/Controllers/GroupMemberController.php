<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupMemberController extends Controller
{
    public function destroy(Request $request, Group $group, User $user): RedirectResponse
    {
        abort_unless($this->isMember($request, $group), 403);

        $isSelf = $request->user()->is($user);

        if (! $isSelf) {
            abort_unless($this->isAdmin($request, $group), 403);
        }

        $isOnlyAdmin = $group->members()
            ->wherePivot('role', 'admin')
            ->whereKeyNot($user->id)
            ->doesntExist();

        if ($isOnlyAdmin && $group->members()->wherePivot('role', 'admin')->whereKey($user->id)->exists()) {
            $oldestMember = $group->members()
                ->wherePivot('role', 'member')
                ->orderByPivot('created_at', 'asc')
                ->first();

            if ($oldestMember) {
                $group->members()->updateExistingPivot($oldestMember->id, ['role' => 'admin']);
            }
        }

        $remainingCount = $group->members()->whereKeyNot($user->id)->count();

        if ($remainingCount === 0) {
            $group->update(['status' => 'closed']);
        }

        $group->members()->detach($user->id);

        if ($isSelf) {
            $remainingMembers = $group->members()->whereKeyNot($user->id)->get();
            foreach ($remainingMembers as $member) {
                Notification::query()->create([
                    'user_id' => $member->id,
                    'type' => 'member.left',
                    'message' => "{$user->name} keluar dari grup {$group->name}",
                    'link' => route('groups.show', $group),
                ]);
            }
        } else {
            Notification::query()->create([
                'user_id' => $user->id,
                'type' => 'member.removed',
                'message' => "Kamu dikeluarkan dari grup {$group->name}",
                'link' => null,
            ]);
        }

        return redirect()->route('groups.index');
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
