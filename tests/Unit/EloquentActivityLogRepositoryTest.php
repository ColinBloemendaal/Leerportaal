<?php

declare(strict_types=1);

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Repositories\Eloquent\EloquentActivityLogRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('paginates and filters activity log entries by event', function (): void {
    Reseller::factory()->create(); // logs a 'created' Reseller activity
    $klant = ResellerKlant::factory()->create(); // logs a 'created' ResellerKlant activity
    $klant->update(['name' => 'Renamed']); // logs an 'updated' activity

    $repository = app(EloquentActivityLogRepository::class);

    $updated = $repository->paginate(new FilterRequestData(search: null, sort: null, sortDirection: 'asc', filters: ['event' => 'updated']));

    expect($updated->total())->toBe(1);
});

it('filters by actor (causer_id)', function (): void {
    // Reseller pre-created and passed explicitly to both -- ResellerKlant's
    // own factory would otherwise create a *nested* Reseller too (it also
    // uses HasAuditLog), logging an extra activity per create() call and
    // throwing off the expected count.
    $reseller = Reseller::factory()->create();
    $actorA = User::factory()->create();
    $actorB = User::factory()->create();

    $this->actingAs($actorA);
    ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);

    $this->actingAs($actorB);
    ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);

    $repository = app(EloquentActivityLogRepository::class);
    $result = $repository->paginate(new FilterRequestData(search: null, sort: null, sortDirection: 'asc', filters: ['causer_id' => (string) $actorA->id]));

    expect($result->total())->toBe(1);
});

it('filters by the causer\'s reseller', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();
    $staffA = User::factory()->create(['reseller_id' => $resellerA->id]);
    $staffB = User::factory()->create(['reseller_id' => $resellerB->id]);

    $this->actingAs($staffA);
    ResellerKlant::factory()->create(['reseller_id' => $resellerA->id]);

    $this->actingAs($staffB);
    ResellerKlant::factory()->create(['reseller_id' => $resellerB->id]);

    $repository = app(EloquentActivityLogRepository::class);
    $result = $repository->paginate(new FilterRequestData(search: null, sort: null, sortDirection: 'asc', filters: ['reseller_id' => (string) $resellerA->id]));

    expect($result->total())->toBe(1);
});

it('filters by a date range', function (): void {
    // Backdate the reseller's own creation activity too -- it's just
    // fixture setup, not the entity under test, and would otherwise
    // count as a second recent activity matching the date filter below.
    $reseller = Reseller::factory()->create();
    $reseller->activitiesAsSubject()->update(['created_at' => now()->subDays(10)]);

    $old = ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);
    $old->activitiesAsSubject()->update(['created_at' => now()->subDays(10)]);

    ResellerKlant::factory()->create(['reseller_id' => $reseller->id]); // created "now"

    $repository = app(EloquentActivityLogRepository::class);
    $result = $repository->paginate(new FilterRequestData(
        search: null,
        sort: null,
        sortDirection: 'asc',
        filters: ['date_from' => now()->subDay()->toDateString()],
    ));

    expect($result->total())->toBe(1);
});
