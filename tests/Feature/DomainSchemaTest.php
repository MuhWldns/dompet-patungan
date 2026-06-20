<?php

use Illuminate\Support\Facades\Schema;

test('domain tables and user role fields exist', function () {
    expect(Schema::hasColumns('users', ['role', 'is_active']))->toBeTrue();
    expect(Schema::hasTable('groups'))->toBeTrue();
    expect(Schema::hasTable('group_members'))->toBeTrue();
    expect(Schema::hasTable('expenses'))->toBeTrue();
    expect(Schema::hasTable('expense_splits'))->toBeTrue();
    expect(Schema::hasTable('payments'))->toBeTrue();
    expect(Schema::hasTable('settlements'))->toBeTrue();
    expect(Schema::hasTable('notifications'))->toBeTrue();
});
