<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('belongs to a reseller by default via the factory', function (): void {
    $user = User::factory()->create();

    expect($user->reseller_id)->not->toBeNull()
        ->and($user->reseller)->toBeInstanceOf(Reseller::class);
});

it('can be platform staff with no reseller', function (): void {
    $user = User::factory()->platformStaff()->create();

    expect($user->reseller_id)->toBeNull()
        ->and($user->resellerklant_id)->toBeNull();
});

it('soft deletes', function (): void {
    $user = User::factory()->create();

    $user->delete();

    expect(User::find($user->id))->toBeNull()
        ->and(User::withTrashed()->find($user->id))->not->toBeNull();
});
