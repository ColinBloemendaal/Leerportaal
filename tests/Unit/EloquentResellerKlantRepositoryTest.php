<?php

declare(strict_types=1);

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Repositories\Eloquent\EloquentResellerKlantRepository;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('searches and sorts klanten for the current reseller', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    ResellerKlant::factory()->create(['reseller_id' => $reseller->id, 'name' => 'Zebra BV']);
    ResellerKlant::factory()->create(['reseller_id' => $reseller->id, 'name' => 'Acme BV']);

    $repository = app(EloquentResellerKlantRepository::class);

    $result = $repository->paginate(new FilterRequestData(search: null, sort: null, sortDirection: 'asc', filters: []));

    expect(collect($result->items())->pluck('name')->all())->toBe(['Acme BV', 'Zebra BV']);
});
