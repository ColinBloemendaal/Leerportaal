<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\ResellerTheme;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

it('persists custom css through the full FormRequest -> DTO -> Action -> Repository chain', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/theme', [
            'primary_color' => '#123456',
            'custom_css' => '.btn { border-radius: 0; }',
        ])
        ->assertRedirect('/settings/theme');

    $this->assertDatabaseHas('reseller_themes', [
        'reseller_id' => $this->reseller->id,
        'custom_css' => '.btn { border-radius: 0; }',
    ]);
});

it('rejects custom css that exceeds the hard character limit', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/theme', [
            'primary_color' => '#123456',
            'custom_css' => str_repeat('a', 10001),
        ])
        ->assertSessionHasErrors('custom_css');
});

it('rejects custom css that attempts to break out of the style block', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/theme', [
            'primary_color' => '#123456',
            'custom_css' => 'body { color: red; } </style><script>alert(1)</script>',
        ])
        ->assertSessionHasErrors('custom_css');
});

it('persists email branding through the full FormRequest -> DTO -> Action -> Repository chain', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/theme', [
            'primary_color' => '#123456',
            'sender_name' => 'Acme Support Team',
            'reply_to_email' => 'support@acme.example',
        ])
        ->assertRedirect('/settings/theme');

    $this->assertDatabaseHas('reseller_themes', [
        'reseller_id' => $this->reseller->id,
        'sender_name' => 'Acme Support Team',
        'reply_to_email' => 'support@acme.example',
    ]);
});

it('rejects an invalid reply-to email address', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/theme', [
            'primary_color' => '#123456',
            'reply_to_email' => 'not-an-email',
        ])
        ->assertSessionHasErrors('reply_to_email');
});

it('persists footer content through the full FormRequest -> DTO -> Action -> Repository chain', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/theme', [
            'primary_color' => '#123456',
            'footer_text' => '© Acme Training. All rights reserved.',
            'support_email' => 'support@acme.example',
            'terms_url' => 'https://acme.example/terms',
            'privacy_url' => 'https://acme.example/privacy',
        ])
        ->assertRedirect('/settings/theme');

    $this->assertDatabaseHas('reseller_themes', [
        'reseller_id' => $this->reseller->id,
        'footer_text' => '© Acme Training. All rights reserved.',
        'support_email' => 'support@acme.example',
        'terms_url' => 'https://acme.example/terms',
        'privacy_url' => 'https://acme.example/privacy',
    ]);
});

it('rejects an invalid terms url', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/theme', [
            'primary_color' => '#123456',
            'terms_url' => 'not-a-url',
        ])
        ->assertSessionHasErrors('terms_url');
});

it('rejects footer text that attempts to break out of the wrapping markup', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/theme', [
            'primary_color' => '#123456',
            'footer_text' => '<script>alert(1)</script>',
        ])
        ->assertSessionHasErrors('footer_text');
});

it('uploads a logo and serves it via the public branding route', function (): void {
    Storage::fake('local');

    $this->actingAs($this->user)
        ->put('/settings/theme', [
            'primary_color' => '#0d6efd',
            'logo' => UploadedFile::fake()->image('logo.png', 64, 64),
        ])
        ->assertRedirect('/settings/theme');

    $theme = ResellerTheme::query()->where('reseller_id', $this->reseller->id)->firstOrFail();
    expect($theme->logo_path)->toBe("reseller-themes/{$this->reseller->id}/logo.png");

    $this->get("/branding/{$this->reseller->slug}/logo")
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

it('rejects a logo that is too small', function (): void {
    Storage::fake('local');

    $this->actingAs($this->user)
        ->put('/settings/theme', [
            'primary_color' => '#0d6efd',
            'logo' => UploadedFile::fake()->image('logo.png', 10, 10),
        ])
        ->assertSessionHasErrors('logo');
});

it('rejects a logo with a disallowed file type', function (): void {
    Storage::fake('local');

    $this->actingAs($this->user)
        ->put('/settings/theme', [
            'primary_color' => '#0d6efd',
            'logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf'),
        ])
        ->assertSessionHasErrors('logo');
});

it('404s the branding route for a reseller with no logo uploaded', function (): void {
    $this->get("/branding/{$this->reseller->slug}/logo")->assertNotFound();
});

it('404s the branding route for an unknown reseller slug', function (): void {
    $this->get('/branding/does-not-exist/logo')->assertNotFound();
});

it('denies platform staff from viewing or updating the theme', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/settings/theme')->assertForbidden();
    $this->actingAs($staff)->put('/settings/theme', ['primary_color' => '#000000'])->assertForbidden();
});

it('redirects guests to login', function (): void {
    $this->get('/settings/theme')->assertRedirect('/login');
});
