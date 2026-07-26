<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionEnum;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

/**
 * Data migration, not a seeder: the permission catalog is a deploy-time
 * invariant the app depends on existing (like a lookup table), not
 * mutable business data -- so it must run via `php artisan migrate`
 * (already in deploy/ploi-deploy.sh) rather than depend on `db:seed`
 * ever being invoked.
 *
 * The permissions table itself isn't team-scoped (only roles and the
 * model_has_* pivots are, per config/permission.php) -- this seeds each
 * permission name once, globally.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (PermissionEnum::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::query()
            ->whereIn('name', array_map(fn (PermissionEnum $permission): string => $permission->value, PermissionEnum::cases()))
            ->delete();
    }
};
