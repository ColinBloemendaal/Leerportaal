<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

afterEach(function (): void {
    RateLimiter::clear('login');
});

it('locks out login after 5 failed attempts for the same email+IP', function (): void {
    $reseller = Reseller::factory()->create();
    $user = User::factory()->create(['reseller_id' => $reseller->id, 'password' => Hash::make('correct-password')]);
    app(TenantContext::class)->set($reseller);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');
    }

    // The 6th attempt is throttled before credentials are even checked,
    // even with the correct password.
    $this->post('/login', ['email' => $user->email, 'password' => 'correct-password'])
        ->assertStatus(429);

    $this->assertGuest();
});

it('does not let attempts against one account exhaust another account tried from the same IP', function (): void {
    $reseller = Reseller::factory()->create();
    $victim = User::factory()->create(['reseller_id' => $reseller->id, 'password' => Hash::make('correct-password')]);
    $bystander = User::factory()->create(['reseller_id' => $reseller->id, 'password' => Hash::make('correct-password')]);
    app(TenantContext::class)->set($reseller);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', ['email' => $victim->email, 'password' => 'wrong-password']);
    }

    $this->post('/login', ['email' => $bystander->email, 'password' => 'correct-password'])
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($bystander);
});
