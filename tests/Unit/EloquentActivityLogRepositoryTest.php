<?php

declare(strict_types=1);

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\Reseller;
use App\Models\ResellerKlant;
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
