<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Groups', [
            'groups' => Group::query()
                ->select(['id', 'creator_id', 'name', 'description', 'status', 'target_amount', 'created_at'])
                ->with('creator:id,name,email')
                ->withCount(['members', 'expenses', 'settlements'])
                ->latest()
                ->paginate(20),
            'summary' => [
                'total' => Group::query()->count(),
                'active' => Group::query()->where('status', 'active')->count(),
                'settled' => Group::query()->where('status', 'settled')->count(),
                'closed' => Group::query()->where('status', 'closed')->count(),
            ],
        ]);
    }
}
