<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Impersonation;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;

it('starts an impersonation session for an authorized impersonator', function (): void {
    $reseller = Reseller::factory()->create();
    $staff = User::factory()->create(['reseller_id' => $reseller->id]);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();
    $target = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);

    $this->actingAs($staff)
        ->post("/impersonate/{$target->id}", ['reason' => 'Troubleshooting a login issue'])
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($target);
    $this->assertDatabaseHas('impersonations', [
        'impersonator_user_id' => $staff->id,
        'impersonated_user_id' => $target->id,
        'reason' => 'Troubleshooting a login issue',
    ]);
});

it('denies starting an impersonation session without authorization', function (): void {
    $reseller = Reseller::factory()->create();
    $staff = User::factory()->create(['reseller_id' => $reseller->id]);

    $otherReseller = Reseller::factory()->create();
    $otherKlant = ResellerKlant::factory()->for($otherReseller, 'reseller')->create();
    $target = User::factory()->create(['reseller_id' => $otherReseller->id, 'resellerklant_id' => $otherKlant->id]);

    $this->actingAs($staff)
        ->post("/impersonate/{$target->id}", ['reason' => 'Not allowed'])
        ->assertForbidden();

    $this->assertAuthenticatedAs($staff);
});

it('requires a reason', function (): void {
    $reseller = Reseller::factory()->create();
    $staff = User::factory()->create(['reseller_id' => $reseller->id]);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();
    $target = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);

    $this->actingAs($staff)
        ->post("/impersonate/{$target->id}", ['reason' => ''])
        ->assertSessionHasErrors('reason');

    $this->assertAuthenticatedAs($staff);
});

it('stops an impersonation session and restores the impersonator', function (): void {
    $reseller = Reseller::factory()->create();
    $staff = User::factory()->create(['reseller_id' => $reseller->id]);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();
    $target = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);

    $this->actingAs($staff)->post("/impersonate/{$target->id}", ['reason' => 'Support']);

    $this->delete('/impersonate')->assertRedirect('/');

    $this->assertAuthenticatedAs($staff);
    $this->assertDatabaseHas('impersonations', [
        'impersonator_user_id' => $staff->id,
        'impersonated_user_id' => $target->id,
    ]);
    expect(Impersonation::query()->where('impersonator_user_id', $staff->id)->first()->ended_at)->not->toBeNull();
});

it('shares impersonation banner data while impersonating, and null otherwise', function (): void {
    $reseller = Reseller::factory()->create();
    $staff = User::factory()->create(['reseller_id' => $reseller->id, 'name' => 'Staff Person']);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();
    $target = User::factory()->create([
        'reseller_id' => $reseller->id,
        'resellerklant_id' => $klant->id,
        'name' => 'Target Person',
    ]);

    $this->actingAs($staff)->post("/impersonate/{$target->id}", ['reason' => 'Support']);

    $this->get('/klanten')->assertInertia(fn ($page) => $page
        ->where('impersonation.impersonatorName', 'Staff Person')
        ->where('impersonation.targetName', 'Target Person'));

    $this->delete('/impersonate');

    $this->get('/klanten')->assertInertia(fn ($page) => $page->where('impersonation', null));
});

it('auto-ends the session past the hard session limit and restores the impersonator', function (): void {
    config(['impersonation.session_minutes' => 15]);

    $reseller = Reseller::factory()->create();
    $staff = User::factory()->create(['reseller_id' => $reseller->id]);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();
    $target = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);

    $this->actingAs($staff)->post("/impersonate/{$target->id}", ['reason' => 'Support']);
    $this->assertAuthenticatedAs($target);

    $this->travel(16)->minutes();

    $this->get('/klanten')->assertRedirect('/');

    $this->assertAuthenticatedAs($staff);
    expect(Impersonation::query()->where('impersonator_user_id', $staff->id)->first()->ended_at)->not->toBeNull();
});

it('does not end the session before the hard session limit', function (): void {
    config(['impersonation.session_minutes' => 15]);

    $reseller = Reseller::factory()->create();
    $staff = User::factory()->create(['reseller_id' => $reseller->id]);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();
    $target = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);

    $this->actingAs($staff)->post("/impersonate/{$target->id}", ['reason' => 'Support']);

    $this->travel(10)->minutes();

    $this->get('/klanten');

    $this->assertAuthenticatedAs($target);
});

it('lets a super admin start and stop impersonation via the full HTTP flow', function (): void {
    $superAdmin = User::factory()->platformRole(Role::SuperAdmin)->twoFactorEnabled()->create();
    $target = User::factory()->create();

    $this->actingAs($superAdmin)
        ->post("/impersonate/{$target->id}", ['reason' => 'Platform support'])
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($target);

    $this->delete('/impersonate')->assertRedirect('/');
    $this->assertAuthenticatedAs($superAdmin);
});
