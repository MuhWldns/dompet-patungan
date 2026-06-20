<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Settlement;
use App\Services\SettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettlementController extends Controller
{
    public function show(Request $request, Group $group): Response
    {
        abort_unless($this->isMember($request, $group), 403);

        return Inertia::render('settlements/Show', [
            'group' => $group->only(['id', 'name']),
            'settlement' => $group->settlements()->latest('generated_at')->first(),
            'isAdmin' => $this->isAdmin($request, $group),
        ]);
    }

    public function generate(Request $request, Group $group, SettlementService $settlementService): RedirectResponse
    {
        abort_unless($this->isAdmin($request, $group), 403);

        Settlement::query()->create([
            'group_id' => $group->id,
            'generated_by' => $request->user()->id,
            'debt_details' => $settlementService->calculate($group),
            'generated_at' => now(),
        ]);

        return redirect()->route('settlements.show', $group);
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
