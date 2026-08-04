<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    config(['gdpr.dpa_version' => '2026-08-04']);
    $this->reseller = Reseller::factory()->create();
    $this->user = User::factory()->create(['reseller_id' => $this->reseller->id]);
});

it('shows the DPA document and flags that it needs accepting', function (): void {
    app(TenantContext::class)->set($this->reseller);

    $this->actingAs($this->user)
        ->get('/settings/dpa')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Settings/Dpa')
            ->where('needsAcceptance', true)
            ->where('currentVersion', '2026-08-04')
            ->where('acceptedVersion', null));
});

it('lets a reseller-side user accept the current DPA', function (): void {
    app(TenantContext::class)->set($this->reseller);

    $this->actingAs($this->user)
        ->post('/settings/dpa/accept')
        ->assertRedirect('/settings/dpa');

    $this->reseller->refresh();

    expect($this->reseller->dpa_accepted_version)->toBe('2026-08-04')
        ->and($this->reseller->dpa_accepted_by_user_id)->toBe($this->user->id);

    $this->actingAs($this->user)
        ->get('/settings/dpa')
        ->assertInertia(fn (AssertableInertia $page) => $page->where('needsAcceptance', false));
});

it('denies a user from a different reseller', function (): void {
    app(TenantContext::class)->set($this->reseller);
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)->get('/settings/dpa')->assertForbidden();
    $this->actingAs($otherUser)->post('/settings/dpa/accept')->assertForbidden();
});

it('404s when there is no ambient tenant to resolve at all', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/settings/dpa')->assertNotFound();
});
