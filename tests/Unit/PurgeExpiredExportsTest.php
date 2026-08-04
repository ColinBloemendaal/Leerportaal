<?php

declare(strict_types=1);

use App\Actions\Gdpr\PurgeExpiredExports;
use App\Models\Export;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    config(['gdpr.retention.expired_exports_grace_days' => 7]);
    Storage::fake('local');
});

it('deletes the file and row for an export expired past the grace period', function (): void {
    $reseller = Reseller::factory()->create();
    Storage::disk('local')->put('exports/old.csv', 'a,b,c');
    $export = Export::factory()->for($reseller)->create([
        'disk' => 'local',
        'path' => 'exports/old.csv',
        'expires_at' => now()->subDays(10),
    ]);

    $deleted = app(PurgeExpiredExports::class)($reseller);

    expect($deleted)->toBe(1);
    Storage::disk('local')->assertMissing('exports/old.csv');
    expect(Export::withTrashed()->find($export->id))->toBeNull();
});

it('leaves an export still within its grace period untouched', function (): void {
    $reseller = Reseller::factory()->create();
    Export::factory()->for($reseller)->create([
        'disk' => 'local',
        'path' => 'exports/recent.csv',
        'expires_at' => now()->subDays(2),
    ]);

    $deleted = app(PurgeExpiredExports::class)($reseller);

    expect($deleted)->toBe(0)
        ->and(Export::query()->count())->toBe(1);
});

it('leaves a not-yet-expired export untouched', function (): void {
    $reseller = Reseller::factory()->create();
    Export::factory()->for($reseller)->create(['expires_at' => now()->addDay()]);

    $deleted = app(PurgeExpiredExports::class)($reseller);

    expect($deleted)->toBe(0);
});

it('scopes to platform-context exports when given a null reseller', function (): void {
    Export::factory()->create(['reseller_id' => null, 'expires_at' => now()->subDays(10)]);
    $reseller = Reseller::factory()->create();
    Export::factory()->for($reseller)->create(['expires_at' => now()->subDays(10)]);

    $deleted = app(PurgeExpiredExports::class)(null);

    expect($deleted)->toBe(1);
});
