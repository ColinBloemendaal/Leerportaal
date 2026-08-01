<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\ResellerTheme;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    $this->reseller = Reseller::factory()->create();
    $this->user = User::factory()->create(['reseller_id' => $this->reseller->id]);
    app(TenantContext::class)->set($this->reseller);
});

it('shows the schema default when the reseller has no theme yet', function (): void {
    $this->actingAs($this->user)
        ->get('/settings/theme')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Settings/Theme')
            ->where('theme.data.primary_color', '#0d6efd')
            ->where('theme.data.secondary_color', null));
});

it('shows the persisted theme when one exists', function (): void {
    ResellerTheme::factory()->for($this->reseller, 'reseller')->create(['primary_color' => '#abcdef']);

    $this->actingAs($this->user)
        ->get('/settings/theme')
        ->assertInertia(fn (AssertableInertia $page) => $page->where('theme.data.primary_color', '#abcdef'));
});

it('creates a theme through the full FormRequest -> DTO -> Action -> Repository chain', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/theme', [
            'primary_color' => '#123456',
            'secondary_color' => '#654321',
            'accent_color' => '',
            'font_family' => 'Inter, system-ui',
        ])
        ->assertRedirect('/settings/theme');

    $this->assertDatabaseHas('reseller_themes', [
        'reseller_id' => $this->reseller->id,
        'primary_color' => '#123456',
        'secondary_color' => '#654321',
        'font_family' => 'Inter, system-ui',
    ]);
});

it('rejects an invalid hex color', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/theme', ['primary_color' => 'not-a-color'])
        ->assertSessionHasErrors('primary_color');
});

it('denies platform staff from viewing or updating the theme', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/settings/theme')->assertForbidden();
    $this->actingAs($staff)->put('/settings/theme', ['primary_color' => '#000000'])->assertForbidden();
});

it('redirects guests to login', function (): void {
    $this->get('/settings/theme')->assertRedirect('/login');
});
