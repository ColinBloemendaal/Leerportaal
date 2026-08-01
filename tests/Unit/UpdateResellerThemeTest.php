<?php

declare(strict_types=1);

use App\Actions\Theming\UpdateResellerTheme;
use App\DataTransferObjects\Theming\UpdateResellerThemeData;
use App\Models\Reseller;
use App\Models\ResellerTheme;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function updateThemeAction(): UpdateResellerTheme
{
    return new UpdateResellerTheme(app(TenantContext::class), app(FilesystemFactory::class));
}

it('creates a theme for a reseller that has none yet', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $theme = (updateThemeAction())(new UpdateResellerThemeData(
        primaryColor: '#112233',
        secondaryColor: '#445566',
        accentColor: null,
        fontFamily: null,
        logo: null,
        favicon: null,
        loginBackground: null,
        customCss: null,
    ));

    expect($theme->reseller_id)->toBe($reseller->id)
        ->and($theme->primary_color)->toBe('#112233')
        ->and($theme->secondary_color)->toBe('#445566');

    expect(ResellerTheme::query()->where('reseller_id', $reseller->id)->count())->toBe(1);
});

it('updates the existing theme in place rather than creating a duplicate', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $existing = ResellerTheme::factory()->for($reseller, 'reseller')->create(['primary_color' => '#000000']);

    $theme = (updateThemeAction())(new UpdateResellerThemeData(
        primaryColor: '#ffffff',
        secondaryColor: null,
        accentColor: null,
        fontFamily: null,
        logo: null,
        favicon: null,
        loginBackground: null,
        customCss: null,
    ));

    expect($theme->id)->toBe($existing->id)
        ->and($theme->primary_color)->toBe('#ffffff')
        ->and(ResellerTheme::query()->where('reseller_id', $reseller->id)->count())->toBe(1);
});

it('persists custom css alongside the other theme attributes', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $theme = (updateThemeAction())(new UpdateResellerThemeData(
        primaryColor: '#112233',
        secondaryColor: null,
        accentColor: null,
        fontFamily: null,
        logo: null,
        favicon: null,
        loginBackground: null,
        customCss: '.btn { border-radius: 0; }',
    ));

    expect($theme->custom_css)->toBe('.btn { border-radius: 0; }');
});

it('stores an uploaded logo on the private disk and records its path', function (): void {
    Storage::fake('local');
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $theme = (updateThemeAction())(new UpdateResellerThemeData(
        primaryColor: '#0d6efd',
        secondaryColor: null,
        accentColor: null,
        fontFamily: null,
        logo: UploadedFile::fake()->create('logo.png', 10, 'image/png'),
        favicon: null,
        loginBackground: null,
        customCss: null,
    ));

    expect($theme->logo_path)->toBe("reseller-themes/{$reseller->id}/logo.png");
    Storage::disk('local')->assertExists($theme->logo_path);
});

it('deletes the previous logo when a new one replaces it', function (): void {
    Storage::fake('local');
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $first = (updateThemeAction())(new UpdateResellerThemeData(
        primaryColor: '#0d6efd',
        secondaryColor: null,
        accentColor: null,
        fontFamily: null,
        logo: UploadedFile::fake()->create('logo.png', 10, 'image/png'),
        favicon: null,
        loginBackground: null,
        customCss: null,
    ));
    $oldPath = $first->logo_path;

    $second = (updateThemeAction())(new UpdateResellerThemeData(
        primaryColor: '#0d6efd',
        secondaryColor: null,
        accentColor: null,
        fontFamily: null,
        logo: UploadedFile::fake()->create('logo.jpg', 10, 'image/jpeg'),
        favicon: null,
        loginBackground: null,
        customCss: null,
    ));

    Storage::disk('local')->assertMissing($oldPath);
    Storage::disk('local')->assertExists($second->logo_path);
    expect($second->logo_path)->not->toBe($oldPath);
});

it('leaves existing assets untouched when no new file is uploaded', function (): void {
    Storage::fake('local');
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $first = (updateThemeAction())(new UpdateResellerThemeData(
        primaryColor: '#0d6efd',
        secondaryColor: null,
        accentColor: null,
        fontFamily: null,
        logo: UploadedFile::fake()->create('logo.png', 10, 'image/png'),
        favicon: null,
        loginBackground: null,
        customCss: null,
    ));

    $second = (updateThemeAction())(new UpdateResellerThemeData(
        primaryColor: '#ffffff',
        secondaryColor: null,
        accentColor: null,
        fontFamily: null,
        logo: null,
        favicon: null,
        loginBackground: null,
        customCss: null,
    ));

    expect($second->logo_path)->toBe($first->logo_path);
    Storage::disk('local')->assertExists($first->logo_path);
});
