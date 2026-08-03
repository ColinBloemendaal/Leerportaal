<?php

declare(strict_types=1);

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Enums\ResellerStatus;
use App\Models\Reseller;
use App\Repositories\Eloquent\EloquentResellerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('searches, filters, and sorts the paginated reseller index', function (): void {
    Reseller::factory()->create(['name' => 'Acme Training', 'status' => ResellerStatus::Active]);
    Reseller::factory()->create(['name' => 'Beta BV', 'status' => ResellerStatus::Suspended]);
    Reseller::factory()->create(['name' => 'Acme Extra', 'status' => ResellerStatus::Suspended]);

    $repository = app(EloquentResellerRepository::class);

    $filters = new FilterRequestData(search: 'acme', sort: 'name', sortDirection: 'desc', filters: ['status' => 'suspended']);
    $result = $repository->paginate($filters);

    expect($result->total())->toBe(1)
        ->and($result->items()[0]->name)->toBe('Acme Extra');
});

it('defaults to sorting by name with no filters applied', function (): void {
    Reseller::factory()->create(['name' => 'Zebra']);
    Reseller::factory()->create(['name' => 'Apple']);

    $repository = app(EloquentResellerRepository::class);

    $result = $repository->paginate(new FilterRequestData(search: null, sort: null, sortDirection: 'asc', filters: []));

    expect(collect($result->items())->pluck('name')->all())->toBe(['Apple', 'Zebra']);
});
