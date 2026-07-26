<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;

/*
 * "Each role can/cannot reach each area" tested against what's actually
 * built today: every policy so far (ResellerKlantPolicy, UserInvitePolicy)
 * checks the coarse reseller_id !== null / platform_role pattern, not a
 * specific spatie role or permission. So the 7 team roles seeded per
 * reseller are, right now, genuinely interchangeable -- assigning any of
 * them changes nothing about what a user can reach. That's accurate to
 * the current implementation, not a gap in these tests: role-specific
 * access is a later-phase concern (once courses/billing/etc. exist and
 * their policies actually check $user->hasRole(...) or permissions).
 */
dataset('policy-gated areas', [
    'klanten' => ['/klanten'],
    'invites' => ['/invites'],
]);

it('lets a reseller-side user reach the area regardless of which team role they hold', function (string $path): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    foreach (Role::teamRoles() as $role) {
        $user = User::factory()->create(['reseller_id' => $reseller->id]);
        $user->assignRole($role->value);

        $this->actingAs($user)->get($path)->assertOk();
    }
})->with('policy-gated areas');

it('lets a reseller-side user with no role at all still reach the area', function (string $path): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get($path)->assertOk();
})->with('policy-gated areas');

it('denies platform staff from reaching the area, regardless of platform_role', function (string $path): void {
    foreach ([null, Role::PlatformAdmin, Role::PlatformAuthor, Role::Support] as $platformRole) {
        $staff = User::factory()->platformStaff()->twoFactorEnabled()->create(['platform_role' => $platformRole]);

        $this->actingAs($staff)->get($path)->assertForbidden();
    }
})->with('policy-gated areas');

it('lets a super admin reach the area via the Gate::before bypass', function (string $path): void {
    $superAdmin = User::factory()->platformRole(Role::SuperAdmin)->twoFactorEnabled()->create();

    $this->actingAs($superAdmin)->get($path)->assertOk();
})->with('policy-gated areas');
