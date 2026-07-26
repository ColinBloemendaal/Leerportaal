<?php

declare(strict_types=1);

use App\Actions\Auth\StartImpersonation;
use App\Actions\Auth\StopImpersonation;
use App\DataTransferObjects\Auth\StartImpersonationData;
use App\Models\Impersonation;
use App\Models\User;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('creates an impersonation record, stashes session markers, and logs in as the target', function (): void {
    $impersonator = User::factory()->create(['name' => 'Alice Admin']);
    $target = User::factory()->create(['name' => 'Bob Cursist']);

    $impersonation = (new StartImpersonation(app(StatefulGuard::class)))(new StartImpersonationData(
        impersonatorUserId: $impersonator->id,
        targetUserId: $target->id,
        reason: 'Debugging a support ticket',
    ));

    expect($impersonation->impersonator_user_id)->toBe($impersonator->id)
        ->and($impersonation->impersonated_user_id)->toBe($target->id)
        ->and($impersonation->reason)->toBe('Debugging a support ticket')
        ->and($impersonation->isActive())->toBeTrue();

    expect(session('impersonation_id'))->toBe($impersonation->id)
        ->and(session('impersonator_id'))->toBe($impersonator->id)
        ->and(session('impersonator_name'))->toBe('Alice Admin')
        ->and(session('impersonated_name'))->toBe('Bob Cursist');

    $this->assertAuthenticatedAs($target);
});

it('ends the impersonation, restores the impersonator, and clears session markers', function (): void {
    $impersonator = User::factory()->create();
    $target = User::factory()->create();
    $impersonation = Impersonation::factory()->create([
        'impersonator_user_id' => $impersonator->id,
        'impersonated_user_id' => $target->id,
    ]);

    session([
        'impersonation_id' => $impersonation->id,
        'impersonator_id' => $impersonator->id,
        'impersonator_name' => $impersonator->name,
        'impersonated_name' => $target->name,
    ]);

    (new StopImpersonation(app(StatefulGuard::class)))($impersonation->id, $impersonator->id);

    expect($impersonation->fresh()->isActive())->toBeFalse();
    $this->assertAuthenticatedAs($impersonator);
    expect(session()->has('impersonation_id'))->toBeFalse()
        ->and(session()->has('impersonator_id'))->toBeFalse()
        ->and(session()->has('impersonator_name'))->toBeFalse()
        ->and(session()->has('impersonated_name'))->toBeFalse();
});
