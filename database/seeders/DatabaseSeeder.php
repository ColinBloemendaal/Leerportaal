<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->seedSuperAdmin();
    }

    /**
     * Platform super admin, driven entirely by config/app.php's
     * admin_seed (ADMIN_SEED_EMAIL / ADMIN_SEED_PASSWORD in .env). Skips
     * silently when either is unset -- see CLAUDE.md §7 on not committing
     * credentials.
     */
    private function seedSuperAdmin(): void
    {
        $email = config('app.admin_seed.email');
        $password = config('app.admin_seed.password');

        if (! $email || ! $password) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Colin Bloemendaal',
                'password' => $password,
                'reseller_id' => null,
                'platform_role' => Role::SuperAdmin,
                'email_verified_at' => Date::now(),
            ],
        );
    }
}
