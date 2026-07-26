<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('seeds every permission in the catalog exactly once', function (): void {
    foreach (PermissionEnum::cases() as $permission) {
        expect(Permission::query()->where(['name' => $permission->value, 'guard_name' => 'web'])->count())->toBe(1);
    }

    expect(Permission::query()->count())->toBe(count(PermissionEnum::cases()));
});
