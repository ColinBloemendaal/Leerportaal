<?php

declare(strict_types=1);

use App\Mail\CourseAssigned;
use App\Mail\Welcome;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\ResellerTheme;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('exposes branding data and falls back to the default color/no logo when the reseller has no theme', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training', 'slug' => 'acme-training']);
    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    $mail = new Welcome($user);

    expect($mail->theme)->toBe('reseller')
        ->and($mail->content()->with['resellerName'])->toBe('Acme Training')
        ->and($mail->content()->with['primaryColor'])->toBe('#0d6efd')
        ->and($mail->content()->with['logoUrl'])->toBeNull();
});

it('builds a logo URL through the public branding route when the reseller has a logo', function (): void {
    $reseller = Reseller::factory()->create(['slug' => 'acme-training']);
    ResellerTheme::factory()->for($reseller, 'reseller')->create([
        'primary_color' => '#ff6600',
        'logo_path' => 'reseller-themes/1/logo.png',
        'footer_text' => 'Acme Training BV, Amsterdam',
    ]);
    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    $mail = new Welcome($user);
    $data = $mail->content()->with;

    expect($data['primaryColor'])->toBe('#ff6600')
        ->and($data['logoUrl'])->toBe(route('branding.logo', 'acme-training'))
        ->and($data['footerText'])->toBe('Acme Training BV, Amsterdam');
});

it('renders the full branded markdown mail without error, including the reseller color and name', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training', 'slug' => 'acme-training']);
    ResellerTheme::factory()->for($reseller, 'reseller')->create(['primary_color' => '#ff6600']);
    $user = User::factory()->create(['reseller_id' => $reseller->id, 'name' => 'Jane Doe']);

    $html = (new Welcome($user))->render();

    expect($html)->toContain('#ff6600')
        ->and($html)->toContain('Acme Training')
        ->and($html)->toContain('Jane Doe');
});

it('renders the CourseAssigned branded mail with the reseller name in the signoff', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training', 'slug' => 'acme-training']);
    app(TenantContext::class)->set($reseller);
    $course = Course::factory()->create(['title' => 'Fire Safety 101']);
    $cursist = User::factory()->create(['reseller_id' => $reseller->id]);
    $assignment = CourseAssignment::factory()->create([
        'reseller_id' => $reseller->id,
        'course_id' => $course->id,
        'user_id' => $cursist->id,
        'deadline_at' => null,
    ]);

    $html = (new CourseAssigned($assignment))->render();

    expect($html)->toContain('Fire Safety 101')
        ->and($html)->toContain('Acme Training');
});
