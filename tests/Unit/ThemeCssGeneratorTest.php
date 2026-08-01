<?php

declare(strict_types=1);

use App\Models\ResellerTheme;
use App\Services\Theming\ThemeCssGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->generator = new ThemeCssGenerator;
});

it('returns an empty string when there is no theme', function (): void {
    expect($this->generator->generate(null))->toBe('');
});

it('renders custom properties for every set color and font', function (): void {
    $theme = ResellerTheme::factory()->make([
        'primary_color' => '#112233',
        'secondary_color' => '#445566',
        'accent_color' => '#789',
        'font_family' => "Inter, 'Segoe UI', system-ui",
    ]);

    $css = $this->generator->generate($theme);

    expect($css)->toContain('--reseller-primary-color: #112233;')
        ->toContain('--reseller-secondary-color: #445566;')
        ->toContain('--reseller-accent-color: #789;')
        ->toContain("--reseller-font-family: Inter, 'Segoe UI', system-ui;");
});

it('omits null attributes rather than rendering empty declarations', function (): void {
    $theme = ResellerTheme::factory()->make([
        'primary_color' => '#112233',
        'secondary_color' => null,
        'accent_color' => null,
        'font_family' => null,
    ]);

    $css = $this->generator->generate($theme);

    expect($css)->toContain('--reseller-primary-color: #112233;')
        ->not->toContain('--reseller-secondary-color')
        ->not->toContain('--reseller-accent-color')
        ->not->toContain('--reseller-font-family');
});

it('rejects a color value that is not a valid hex color, preventing CSS injection', function (): void {
    $theme = ResellerTheme::factory()->make([
        'primary_color' => 'red; } body { display: none; } :root {',
    ]);

    expect($this->generator->generate($theme))->not->toContain('display: none');
});

it('rejects a font family value containing unsafe characters', function (): void {
    $theme = ResellerTheme::factory()->make([
        'primary_color' => '#112233',
        'font_family' => 'Arial</style><script>alert(1)</script>',
    ]);

    $css = $this->generator->generate($theme);

    expect($css)->not->toContain('<script>')
        ->not->toContain('--reseller-font-family');
});

it('appends valid custom css after the root declaration block', function (): void {
    $theme = ResellerTheme::factory()->make([
        'primary_color' => '#112233',
        'custom_css' => '.btn { border-radius: 0; }',
    ]);

    $css = $this->generator->generate($theme);

    expect($css)->toContain('--reseller-primary-color: #112233;')
        ->toContain('.btn { border-radius: 0; }');
});

it('omits custom css entirely when there is none', function (): void {
    $theme = ResellerTheme::factory()->make([
        'primary_color' => '#112233',
        'secondary_color' => null,
        'accent_color' => null,
        'font_family' => null,
        'custom_css' => null,
    ]);

    expect($this->generator->generate($theme))->toBe(':root { --reseller-primary-color: #112233; }');
});

it('drops custom css that fails the safety denylist, even though it was already validated on write', function (): void {
    $theme = ResellerTheme::factory()->make([
        'primary_color' => '#112233',
        // Simulates a row that predates a stricter rule, or was written by
        // a path that skipped validation -- the generator must not trust
        // the database.
        'custom_css' => 'body { background: url(x); } </style><script>alert(1)</script>',
    ]);

    $css = $this->generator->generate($theme);

    expect($css)->not->toContain('<script>')
        ->not->toContain('</style><script>');
});
