<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Mail\UserInvited;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Models\UserInvite;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
    $this->reseller = Reseller::factory()->create();
    $this->user = User::factory()->create(['reseller_id' => $this->reseller->id]);
    app(TenantContext::class)->set($this->reseller);
});

it('lists pending invites for the current reseller', function (): void {
    UserInvite::factory()->create(['reseller_id' => $this->reseller->id, 'name' => 'Pending Person']);
    UserInvite::factory()->accepted()->create(['reseller_id' => $this->reseller->id, 'name' => 'Already Accepted']);

    $this->actingAs($this->user)
        ->get('/invites')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Invites/Index')
            ->where('invites.data.0.name', 'Pending Person')
            ->has('invites.data', 1));
});

it('sends a reseller-staff invite', function (): void {
    $this->actingAs($this->user)
        ->post('/invites', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.test',
            'resellerklant_id' => '',
            'role' => Role::ResellerAdmin->value,
        ])
        ->assertRedirect('/invites');

    $this->assertDatabaseHas('user_invites', [
        'email' => 'jane@example.test',
        'reseller_id' => $this->reseller->id,
        'resellerklant_id' => null,
        'role' => Role::ResellerAdmin->value,
    ]);

    Mail::assertQueued(UserInvited::class);
});

it('sends a klant invite', function (): void {
    $klant = ResellerKlant::factory()->for($this->reseller, 'reseller')->create();

    $this->actingAs($this->user)
        ->post('/invites', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.test',
            'resellerklant_id' => $klant->id,
            'role' => Role::Cursist->value,
        ])
        ->assertRedirect('/invites');

    $this->assertDatabaseHas('user_invites', [
        'email' => 'jane@example.test',
        'resellerklant_id' => $klant->id,
        'role' => Role::Cursist->value,
    ]);
});

it('rejects a role that does not match the invite type', function (): void {
    $klant = ResellerKlant::factory()->for($this->reseller, 'reseller')->create();

    $this->actingAs($this->user)
        ->post('/invites', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.test',
            'resellerklant_id' => $klant->id,
            // ResellerAdmin isn't a valid klant role.
            'role' => Role::ResellerAdmin->value,
        ])
        ->assertSessionHasErrors('role');

    Mail::assertNothingQueued();
});

it('rejects a resellerklant belonging to a different reseller', function (): void {
    $otherReseller = Reseller::factory()->create();
    $otherKlant = ResellerKlant::factory()->for($otherReseller, 'reseller')->create();

    $this->actingAs($this->user)
        ->post('/invites', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.test',
            'resellerklant_id' => $otherKlant->id,
            'role' => Role::Cursist->value,
        ])
        ->assertSessionHasErrors('resellerklant_id');
});

it('revokes a pending invite', function (): void {
    $invite = UserInvite::factory()->create(['reseller_id' => $this->reseller->id]);

    $this->actingAs($this->user)
        ->delete("/invites/{$invite->id}")
        ->assertRedirect('/invites');

    expect(UserInvite::find($invite->id))->toBeNull();
});

it('lists revoked invites separately from pending ones', function (): void {
    $invite = UserInvite::factory()->create(['reseller_id' => $this->reseller->id, 'name' => 'Revoked Person']);
    $invite->delete();

    $this->actingAs($this->user)
        ->get('/invites')
        ->assertInertia(fn ($page) => $page
            ->where('revoked.data.0.name', 'Revoked Person')
            ->has('invites.data', 0));
});

it('restores a revoked invite', function (): void {
    $invite = UserInvite::factory()->create(['reseller_id' => $this->reseller->id]);
    $invite->delete();

    $this->actingAs($this->user)
        ->post("/invites/{$invite->id}/restore")
        ->assertRedirect('/invites');

    $this->assertDatabaseHas('user_invites', ['id' => $invite->id, 'deleted_at' => null]);
});

it('denies platform staff (no reseller) from viewing invites', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)
        ->get('/invites')
        ->assertForbidden();
});

it('redirects guests to login', function (): void {
    $this->get('/invites')->assertRedirect('/login');
});
