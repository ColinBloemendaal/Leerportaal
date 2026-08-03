<?php

declare(strict_types=1);

use App\Models\Export;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('is downloadable only when completed, has a path, and is not expired', function (): void {
    $pending = Export::factory()->create();
    $completed = Export::factory()->completed()->create();
    $expired = Export::factory()->expired()->create();
    $failed = Export::factory()->failed()->create();

    expect($pending->isDownloadable())->toBeFalse()
        ->and($completed->isDownloadable())->toBeTrue()
        ->and($expired->isDownloadable())->toBeFalse()
        ->and($failed->isDownloadable())->toBeFalse();
});

it('reports expiry based on expires_at', function (): void {
    $noExpiry = Export::factory()->create(['expires_at' => null]);
    $expired = Export::factory()->expired()->create();
    $completed = Export::factory()->completed()->create();

    expect($noExpiry->isExpired())->toBeFalse()
        ->and($expired->isExpired())->toBeTrue()
        ->and($completed->isExpired())->toBeFalse();
});
