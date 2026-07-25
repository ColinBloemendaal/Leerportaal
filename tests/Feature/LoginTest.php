<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Hash;

it('shows the login page', function (): void {
    $this->get('/login')->assertOk();
});

it('logs in a reseller user within their own reseller context', function (): void {
    $reseller = Reseller::factory()->create();
    $user = User::factory()->create(['reseller_id' => $reseller->id, 'password' => Hash::make('correct-password')]);
    app(TenantContext::class)->set($reseller);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'correct-password',
    ])->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

it('rejects incorrect credentials', function (): void {
    $reseller = Reseller::factory()->create();
    $user = User::factory()->create(['reseller_id' => $reseller->id, 'password' => Hash::make('correct-password')]);
    app(TenantContext::class)->set($reseller);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('rejects a reseller user logging in from a different reseller context', function (): void {
    $ownReseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();
    $user = User::factory()->create(['reseller_id' => $ownReseller->id, 'password' => Hash::make('correct-password')]);

    app(TenantContext::class)->set($otherReseller);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'correct-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('rejects a reseller user logging in on the unbranded domain', function (): void {
    $reseller = Reseller::factory()->create();
    $user = User::factory()->create(['reseller_id' => $reseller->id, 'password' => Hash::make('correct-password')]);

    // No TenantContext set: unbranded domain.
    $this->post('/login', [
        'email' => $user->email,
        'password' => 'correct-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('allows platform staff to log in on the unbranded domain', function (): void {
    $staff = User::factory()->platformStaff()->create(['password' => Hash::make('correct-password')]);

    $this->post('/login', [
        'email' => $staff->email,
        'password' => 'correct-password',
    ])->assertRedirect('/');

    $this->assertAuthenticatedAs($staff);
});

it('allows platform staff to log in within any reseller context', function (): void {
    $reseller = Reseller::factory()->create();
    $staff = User::factory()->platformStaff()->create(['password' => Hash::make('correct-password')]);

    app(TenantContext::class)->set($reseller);

    $this->post('/login', [
        'email' => $staff->email,
        'password' => 'correct-password',
    ])->assertRedirect('/');

    $this->assertAuthenticatedAs($staff);
});

it('logs out', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/');

    $this->assertGuest();
});
