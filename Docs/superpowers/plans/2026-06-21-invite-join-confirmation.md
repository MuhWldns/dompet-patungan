# Invite Join Confirmation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a normal clickable invite-link flow where users see a confirmation page before joining a group.

**Architecture:** Keep membership mutation in the existing POST join route. Add a GET preview route that validates the invite token, redirects guests through Laravel auth, and renders a new Inertia `groups/Join` page with group summary and membership state.

**Tech Stack:** Laravel 13, Inertia v3, Vue 3, Pest 4, Tailwind CSS v4.

---

## File Structure

- Modify `routes/web.php`: add the GET invite confirmation route before the existing POST join route.
- Modify `app/Http/Controllers/GroupController.php`: add `joinPreview`, reuse existing membership helpers, and pass join page props.
- Create `resources/js/pages/groups/Join.vue`: render the confirmation UI and POST join form.
- Modify `tests/Feature/GroupsTest.php`: add coverage for preview, guest redirect, invalid token, already-member state, and existing POST behavior.

---

### Task 1: Add Failing Backend Tests

**Files:**
- Modify: `tests/Feature/GroupsTest.php`

- [ ] **Step 1: Add tests for invite confirmation behavior**

Append these tests to `tests/Feature/GroupsTest.php`:

```php
test('authenticated users can view group invite confirmation pages with valid tokens', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $owner->id,
        'name' => 'Weekend Trip',
        'description' => 'Shared travel costs',
        'invite_token' => 'weekend-token',
    ]);

    $response = $this
        ->actingAs($member)
        ->get(route('groups.join.show', [$group, 'weekend-token']));

    $response
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('groups/Join')
            ->where('group.id', $group->id)
            ->where('group.name', 'Weekend Trip')
            ->where('group.description', 'Shared travel costs')
            ->where('group.status', 'active')
            ->where('group.members_count', 0)
            ->where('isMember', false)
        );
});

test('guests opening group invite confirmation pages are redirected to login', function () {
    $owner = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $owner->id,
        'name' => 'Guest Invite',
        'invite_token' => 'guest-token',
    ]);

    $response = $this->get(route('groups.join.show', [$group, 'guest-token']));

    $response->assertRedirect(route('login'));
});

test('group invite confirmation pages return not found for invalid tokens', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $owner->id,
        'name' => 'Invalid Invite',
        'invite_token' => 'valid-token',
    ]);

    $response = $this
        ->actingAs($member)
        ->get(route('groups.join.show', [$group, 'wrong-token']));

    $response->assertNotFound();
});

test('existing members can view group invite confirmation pages without duplicate membership', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $owner->id,
        'name' => 'Existing Member Invite',
        'invite_token' => 'existing-token',
    ]);
    $group->members()->attach($member->id, ['role' => 'member']);

    $response = $this
        ->actingAs($member)
        ->get(route('groups.join.show', [$group, 'existing-token']));

    $response
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('groups/Join')
            ->where('isMember', true)
            ->where('group.members_count', 1)
        );

    expect($group->members()->whereKey($member->id)->count())->toBe(1);
});
```

- [ ] **Step 2: Run focused group tests and verify they fail**

Run:

```bash
php artisan test tests/Feature/GroupsTest.php
```

Expected: FAIL because `groups.join.show` is not defined.

---

### Task 2: Add GET Join Preview Route And Controller Method

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/GroupController.php`

- [ ] **Step 1: Add the GET route**

In `routes/web.php`, place this route before the existing POST join route:

```php
Route::get('groups/{group}/join/{token}', [GroupController::class, 'joinPreview'])->name('groups.join.show');
```

The surrounding route block should become:

```php
Route::get('groups', [GroupController::class, 'index'])->name('groups.index');
Route::post('groups', [GroupController::class, 'store'])->name('groups.store');
Route::post('groups/{group}/expenses', [GroupExpenseController::class, 'store'])->name('groups.expenses.store');
Route::get('groups/{group}/join/{token}', [GroupController::class, 'joinPreview'])->name('groups.join.show');
Route::post('groups/{group}/join/{token}', [GroupController::class, 'join'])->name('groups.join');
Route::get('groups/{group}', [GroupController::class, 'show'])->name('groups.show');
```

- [ ] **Step 2: Add `joinPreview` implementation**

In `app/Http/Controllers/GroupController.php`, add this method above `join`:

```php
public function joinPreview(Request $request, Group $group, string $token): Response
{
    abort_unless(hash_equals($group->invite_token, $token), 404);

    $group->loadCount('members');

    return Inertia::render('groups/Join', [
        'group' => [
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description,
            'status' => $group->status,
            'members_count' => $group->members_count,
        ],
        'isMember' => $this->isMember($request, $group),
        'joinUrl' => route('groups.join', [$group, $token]),
        'groupUrl' => route('groups.show', $group),
    ]);
}
```

- [ ] **Step 3: Run focused group tests**

Run:

```bash
php artisan test tests/Feature/GroupsTest.php
```

Expected: PASS for backend route/controller behavior. If the suite attempts to resolve the missing Vue component, proceed to Task 3 and rerun.

---

### Task 3: Add Join Confirmation Vue Page

**Files:**
- Create: `resources/js/pages/groups/Join.vue`

- [ ] **Step 1: Create the page**

Create `resources/js/pages/groups/Join.vue` with:

```vue
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

type Group = {
    id: string;
    name: string;
    description: string | null;
    status: string;
    members_count: number;
};

defineProps<{
    group: Group;
    groupUrl: string;
    isMember: boolean;
    joinUrl: string;
}>();
</script>

<template>
    <Head :title="`Join ${group.name}`" />

    <main class="flex min-h-[calc(100vh-4rem)] items-center justify-center p-4 md:p-8">
        <section class="w-full max-w-3xl overflow-hidden rounded-[2rem] border border-black/10 bg-white">
            <div class="bg-black p-8 text-white md:p-10">
                <p class="text-sm font-semibold tracking-[0.24px] text-white/60 uppercase">
                    Group invite
                </p>
                <h1 class="mt-4 text-4xl font-semibold tracking-[-0.04em] md:text-6xl">
                    Join {{ group.name }}
                </h1>
                <p class="mt-4 max-w-2xl text-white/70">
                    {{ group.description ?? 'You have been invited to join this shared expense group.' }}
                </p>
            </div>

            <div class="grid gap-6 p-6 md:p-8">
                <div class="grid gap-3 text-sm text-black/60 md:grid-cols-2">
                    <div class="rounded-2xl bg-[#f4f4f4] p-4">
                        <p class="font-semibold text-black">Status</p>
                        <p class="mt-1 capitalize">{{ group.status }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#f4f4f4] p-4">
                        <p class="font-semibold text-black">Members</p>
                        <p class="mt-1">{{ group.members_count }} joined</p>
                    </div>
                </div>

                <div v-if="isMember" class="rounded-2xl bg-[#f4f4f4] p-5">
                    <h2 class="text-xl font-semibold text-black">
                        You are already a member
                    </h2>
                    <p class="mt-2 text-black/60">
                        Open the group to view members, expenses, and payments.
                    </p>
                    <Link
                        :href="groupUrl"
                        class="mt-5 inline-flex h-12 items-center rounded-full bg-black px-6 font-semibold text-white"
                    >
                        Open group
                    </Link>
                </div>

                <div v-else class="rounded-2xl bg-[#f4f4f4] p-5">
                    <h2 class="text-xl font-semibold text-black">
                        Confirm before joining
                    </h2>
                    <p class="mt-2 text-black/60">
                        Joining lets other members see your name and include you in shared expenses.
                    </p>
                    <Link
                        :href="joinUrl"
                        as="button"
                        class="mt-5 inline-flex h-12 items-center rounded-full bg-black px-6 font-semibold text-white"
                        method="post"
                    >
                        Join group
                    </Link>
                </div>
            </div>
        </section>
    </main>
</template>
```

- [ ] **Step 2: Run frontend format check and type check**

Run:

```bash
npm run format:check
```

Expected: PASS, or FAIL only with formatting differences in the new file.

Run:

```bash
npm run types:check
```

Expected: PASS.

- [ ] **Step 3: Format if required**

If `npm run format:check` fails, run:

```bash
npm run format
```

Then rerun:

```bash
npm run format:check
```

Expected: PASS.

---

### Task 4: Verify Existing POST Join Behavior Still Works

**Files:**
- Modify only if tests expose a regression: `app/Http/Controllers/GroupController.php`

- [ ] **Step 1: Run the existing join test**

Run:

```bash
php artisan test tests/Feature/GroupsTest.php --filter="users can join groups with invite tokens as members"
```

Expected: PASS.

- [ ] **Step 2: Run the full groups feature suite**

Run:

```bash
php artisan test tests/Feature/GroupsTest.php
```

Expected: PASS.

---

### Task 5: Final Verification

**Files:**
- No code changes expected.

- [ ] **Step 1: Run backend format check**

Run:

```bash
composer lint:check
```

Expected: PASS.

- [ ] **Step 2: Run backend type check**

Run:

```bash
composer types:check
```

Expected: PASS.

- [ ] **Step 3: Run frontend lint check**

Run:

```bash
npm run lint:check
```

Expected: PASS.

- [ ] **Step 4: Run frontend format check**

Run:

```bash
npm run format:check
```

Expected: PASS.

- [ ] **Step 5: Run frontend type check**

Run:

```bash
npm run types:check
```

Expected: PASS.

- [ ] **Step 6: Run focused feature tests**

Run:

```bash
php artisan test tests/Feature/GroupsTest.php
```

Expected: PASS.
