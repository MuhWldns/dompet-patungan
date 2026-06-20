<?php

use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function createGroupWithMembers(): array
{
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Expense Group',
        'invite_token' => fake()->uuid(),
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);
    $group->members()->attach($member->id, ['role' => 'member']);

    return [$group, $admin, $member];
}

test('group admins can create expenses with equal splits and generated payments', function () {
    [$group, $admin, $member] = createGroupWithMembers();

    $response = $this
        ->actingAs($admin)
        ->post(route('groups.expenses.store', $group), [
            'title' => 'Dinner',
            'amount' => '100.00',
            'category' => 'food',
            'date' => '2026-06-21',
            'split_method' => 'equal',
        ]);

    $expense = Expense::query()->where('title', 'Dinner')->firstOrFail();

    $response->assertRedirect(route('groups.show', $group));
    expect($expense->splits()->where('user_id', $admin->id)->value('amount'))->toBe('50.00');
    expect($expense->splits()->where('user_id', $member->id)->value('amount'))->toBe('50.00');
    expect($expense->payments()->count())->toBe(2);
});

test('custom split totals must match the expense amount', function () {
    [$group, $admin, $member] = createGroupWithMembers();

    $response = $this
        ->actingAs($admin)
        ->from(route('groups.show', $group))
        ->post(route('groups.expenses.store', $group), [
            'title' => 'Groceries',
            'amount' => '100.00',
            'category' => 'food',
            'date' => '2026-06-21',
            'split_method' => 'custom',
            'splits' => [
                $admin->id => '20.00',
                $member->id => '30.00',
            ],
        ]);

    $response
        ->assertRedirect(route('groups.show', $group))
        ->assertSessionHasErrors('splits');
});

test('receipts are stored on the public disk', function () {
    Storage::fake('public');
    [$group, $admin] = createGroupWithMembers();

    $this
        ->actingAs($admin)
        ->post(route('groups.expenses.store', $group), [
            'title' => 'Hotel',
            'amount' => '200.00',
            'date' => '2026-06-21',
            'split_method' => 'equal',
            'receipt' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

    $expense = Expense::query()->where('title', 'Hotel')->firstOrFail();

    expect($expense->receipt_path)->not->toBeNull();
    Storage::disk('public')->assertExists($expense->receipt_path);
});

test('non admin members cannot create expenses', function () {
    [$group, , $member] = createGroupWithMembers();

    $response = $this
        ->actingAs($member)
        ->post(route('groups.expenses.store', $group), [
            'title' => 'Denied',
            'amount' => '100.00',
            'date' => '2026-06-21',
            'split_method' => 'equal',
        ]);

    $response->assertForbidden();
});
