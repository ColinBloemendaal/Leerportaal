<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('logs model creation', function (): void {
    $klant = ResellerKlant::factory()->create(['name' => 'Acme BV']);

    $activity = Activity::query()->where('subject_type', ResellerKlant::class)->where('subject_id', $klant->id)->first();

    expect($activity)->not->toBeNull()
        ->and($activity->event)->toBe('created')
        ->and($activity->attribute_changes->get('attributes')['name'])->toBe('Acme BV');
});

it('logs only what actually changed on update', function (): void {
    $klant = ResellerKlant::factory()->create(['name' => 'Old Name']);
    $klant->update(['name' => 'New Name']);

    $activity = Activity::query()
        ->where('subject_type', ResellerKlant::class)
        ->where('subject_id', $klant->id)
        ->where('event', 'updated')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->attribute_changes->get('attributes')['name'])->toBe('New Name')
        ->and($activity->attribute_changes->get('old')['name'])->toBe('Old Name');
});

it('logs deletion', function (): void {
    $reseller = Reseller::factory()->create();
    $reseller->delete();

    $activity = Activity::query()
        ->where('subject_type', Reseller::class)
        ->where('subject_id', $reseller->id)
        ->where('event', 'deleted')
        ->first();

    expect($activity)->not->toBeNull();
});

it('never writes secrets to the audit log, even though they are unguarded attributes', function (): void {
    $user = User::factory()->twoFactorEnabled()->create();

    $activity = Activity::query()->where('subject_type', User::class)->where('subject_id', $user->id)->first();

    $logged = $activity->attribute_changes->get('attributes', []);

    expect($logged)->not->toHaveKey('password')
        ->not->toHaveKey('remember_token')
        ->not->toHaveKey('two_factor_secret')
        ->not->toHaveKey('two_factor_recovery_codes');
});

it('records the authenticated user as the causer', function (): void {
    $actor = User::factory()->create();
    $this->actingAs($actor);

    $klant = ResellerKlant::factory()->create();

    $activity = Activity::query()->where('subject_type', ResellerKlant::class)->where('subject_id', $klant->id)->first();

    expect($activity->causer?->is($actor))->toBeTrue();
});
