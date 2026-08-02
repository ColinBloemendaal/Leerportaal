<?php

declare(strict_types=1);

use App\DataTransferObjects\Filtering\FilterableSpec;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Enums\ResellerStatus;
use App\Models\Reseller;
use App\Support\Filtering\QueryFilterApplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function applyFilters(FilterRequestData $filters, FilterableSpec $spec): array
{
    $applier = new QueryFilterApplier;

    return $applier->apply(Reseller::query(), $filters, $spec)->pluck('name')->all();
}

it('searches across the allowed columns only', function (): void {
    Reseller::factory()->create(['name' => 'Acme Training', 'slug' => 'acme-training']);
    Reseller::factory()->create(['name' => 'Beta BV', 'slug' => 'beta-bv']);

    $filters = new FilterRequestData(search: 'acme', sort: null, sortDirection: 'asc', filters: []);
    $spec = new FilterableSpec(searchableColumns: ['name', 'slug']);

    expect(applyFilters($filters, $spec))->toBe(['Acme Training']);
});

it('ignores a filter field that is not on the allowlist', function (): void {
    Reseller::factory()->create(['name' => 'Acme', 'status' => ResellerStatus::Active]);
    Reseller::factory()->create(['name' => 'Beta', 'status' => ResellerStatus::Suspended]);

    $filters = new FilterRequestData(search: null, sort: null, sortDirection: 'asc', filters: ['status' => ResellerStatus::Suspended->value]);
    $spec = new FilterableSpec(allowedFilters: []);

    // 'status' isn't in allowedFilters, so both rows remain -- it's a
    // no-op, not an error, matching how an index silently drops an
    // unrecognized query param rather than 500ing.
    expect(applyFilters($filters, $spec))->toHaveCount(2);
});

it('applies an allowed filter', function (): void {
    Reseller::factory()->create(['name' => 'Acme', 'status' => ResellerStatus::Active]);
    Reseller::factory()->create(['name' => 'Beta', 'status' => ResellerStatus::Suspended]);

    $filters = new FilterRequestData(search: null, sort: null, sortDirection: 'asc', filters: ['status' => ResellerStatus::Suspended->value]);
    $spec = new FilterableSpec(allowedFilters: ['status']);

    expect(applyFilters($filters, $spec))->toBe(['Beta']);
});

it('sorts by an allowed column and direction', function (): void {
    Reseller::factory()->create(['name' => 'Beta']);
    Reseller::factory()->create(['name' => 'Acme']);

    $filters = new FilterRequestData(search: null, sort: 'name', sortDirection: 'desc', filters: []);
    $spec = new FilterableSpec(allowedSorts: ['name']);

    expect(applyFilters($filters, $spec))->toBe(['Beta', 'Acme']);
});

it('falls back to the spec\'s default sort when the requested one is not allowed', function (): void {
    Reseller::factory()->create(['name' => 'Beta']);
    Reseller::factory()->create(['name' => 'Acme']);

    $filters = new FilterRequestData(search: null, sort: 'id', sortDirection: 'asc', filters: []);
    $spec = new FilterableSpec(allowedSorts: ['name'], defaultSort: 'name');

    expect(applyFilters($filters, $spec))->toBe(['Acme', 'Beta']);
});

it('parses search/sort/direction/filter from a request', function (): void {
    $request = Request::create('/?search=acme&sort=name&direction=DESC&filter[status]=active');

    $filters = FilterRequestData::fromRequest($request);

    expect($filters->search)->toBe('acme')
        ->and($filters->sort)->toBe('name')
        ->and($filters->sortDirection)->toBe('desc')
        ->and($filters->filters)->toBe(['status' => 'active']);
});

it('treats an empty search string as no search at all', function (): void {
    $filters = FilterRequestData::fromRequest(Request::create('/?search='));

    expect($filters->search)->toBeNull();
});
