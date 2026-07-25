<?php

declare(strict_types=1);

use App\Enums\ResellerStatus;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('persists with the expected defaults and casts', function (): void {
    $reseller = Reseller::factory()->create();

    expect($reseller->status)->toBe(ResellerStatus::Active)
        ->and($reseller->settings)->toBe([])
        ->and($reseller->slug)->not->toBeEmpty();
});

it('can be created as suspended', function (): void {
    $reseller = Reseller::factory()->suspended()->create();

    expect($reseller->status)->toBe(ResellerStatus::Suspended);
});

it('soft deletes', function (): void {
    $reseller = Reseller::factory()->create();

    $reseller->delete();

    expect(Reseller::find($reseller->id))->toBeNull()
        ->and(Reseller::withTrashed()->find($reseller->id))->not->toBeNull();
});
