<?php

declare(strict_types=1);

use App\Actions\Auth\EnableTwoFactorAuthentication;
use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

function currentOtpFor(string $secret): string
{
    return app(Google2FA::class)->getCurrentOtp($secret);
}

describe('login-time challenge', function (): void {
    it('redirects to the challenge screen instead of logging in when 2FA is enabled', function (): void {
        $reseller = Reseller::factory()->create();
        $user = User::factory()
            ->twoFactorEnabled()
            ->create(['reseller_id' => $reseller->id, 'password' => Hash::make('correct-password')]);
        app(TenantContext::class)->set($reseller);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect('/two-factor-challenge');

        // AuthenticateUser only verifies credentials via StatefulGuard::once(),
        // which never touches the session -- so no persistent login happened.
        expect(session('login.id'))->toBe($user->id)
            ->and(session()->has('login.remember'))->toBeTrue();
    });

    it('shows the challenge page when a challenge is pending', function (): void {
        $this->withSession(['login.id' => 1])
            ->get('/two-factor-challenge')
            ->assertOk();
    });

    it('redirects away from the challenge page when no challenge is pending', function (): void {
        $this->get('/two-factor-challenge')->assertRedirect('/login');
    });

    it('completes login with a valid TOTP code', function (): void {
        $user = User::factory()->twoFactorEnabled()->create();

        $this->withSession(['login.id' => $user->id, 'login.remember' => false])
            ->post('/two-factor-challenge', ['code' => currentOtpFor((string) $user->two_factor_secret)])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    });

    it('completes login with a valid recovery code and consumes it', function (): void {
        $user = User::factory()->twoFactorEnabled()->create();
        $codes = $user->two_factor_recovery_codes;
        $usedCode = $codes[0];

        $this->withSession(['login.id' => $user->id, 'login.remember' => false])
            ->post('/two-factor-challenge', ['code' => $usedCode])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        expect($user->fresh()->two_factor_recovery_codes)->not->toContain($usedCode);
    });

    it('rejects an invalid code', function (): void {
        $user = User::factory()->twoFactorEnabled()->create();

        $this->withSession(['login.id' => $user->id])
            ->post('/two-factor-challenge', ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    });
});

describe('managing 2FA from settings', function (): void {
    it('generates a secret and unconfirmed recovery codes on enable', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/settings/two-factor')
            ->assertRedirect('/settings/two-factor');

        $user = $user->fresh();
        expect($user->two_factor_secret)->not->toBeNull()
            ->and($user->hasEnabledTwoFactorAuthentication())->toBeFalse();
    });

    it('confirms with a valid code', function (): void {
        $user = User::factory()->create();
        app(EnableTwoFactorAuthentication::class)($user);
        $user = $user->fresh();

        $this->actingAs($user)
            ->post('/settings/two-factor/confirm', ['code' => currentOtpFor((string) $user->two_factor_secret)])
            ->assertRedirect('/settings/two-factor');

        expect($user->fresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();
    });

    it('rejects confirmation with an invalid code', function (): void {
        $user = User::factory()->create();
        app(EnableTwoFactorAuthentication::class)($user);

        $this->actingAs($user->fresh())
            ->post('/settings/two-factor/confirm', ['code' => '000000'])
            ->assertSessionHasErrors('code');

        expect($user->fresh()->hasEnabledTwoFactorAuthentication())->toBeFalse();
    });

    it('disables 2FA and clears the secret and recovery codes', function (): void {
        $user = User::factory()->twoFactorEnabled()->create();

        $this->actingAs($user)
            ->delete('/settings/two-factor')
            ->assertRedirect('/settings/two-factor');

        $user = $user->fresh();
        expect($user->two_factor_secret)->toBeNull()
            ->and($user->two_factor_recovery_codes)->toBeNull()
            ->and($user->hasEnabledTwoFactorAuthentication())->toBeFalse();
    });

    it('regenerates recovery codes', function (): void {
        $user = User::factory()->twoFactorEnabled()->create();
        $original = $user->two_factor_recovery_codes;

        $this->actingAs($user)
            ->post('/settings/two-factor/recovery-codes')
            ->assertRedirect('/settings/two-factor');

        expect($user->fresh()->two_factor_recovery_codes)->not->toBe($original);
    });
});

describe('enforcement middleware', function (): void {
    it('redirects platform staff without confirmed 2FA to setup', function (): void {
        $staff = User::factory()->platformStaff()->create();

        $this->actingAs($staff)
            ->get('/klanten')
            ->assertRedirect('/settings/two-factor');
    });

    it('does not redirect platform staff with confirmed 2FA', function (): void {
        $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

        $this->actingAs($staff)
            ->get('/klanten')
            ->assertForbidden();
    });

    it('does not require 2FA for reseller-side users', function (): void {
        $reseller = Reseller::factory()->create();
        $user = User::factory()->create(['reseller_id' => $reseller->id]);
        app(TenantContext::class)->set($reseller);

        $this->actingAs($user)
            ->get('/klanten')
            ->assertOk();
    });

    it('exempts logout from the 2FA gate', function (): void {
        $staff = User::factory()->platformStaff()->create();

        $this->actingAs($staff)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    });
});
