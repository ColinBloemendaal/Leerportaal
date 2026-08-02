<?php

declare(strict_types=1);

use App\Enums\FilterableResource;
use App\Models\SavedFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('casts resource_type to the enum and filters to an array', function (): void {
    $savedFilter = SavedFilter::factory()->create([
        'resource_type' => FilterableResource::Courses,
        'filters' => ['status' => 'published'],
    ]);

    expect($savedFilter->resource_type)->toBe(FilterableResource::Courses)
        ->and($savedFilter->filters)->toBe(['status' => 'published']);
});

it('soft deletes', function (): void {
    $savedFilter = SavedFilter::factory()->create();
    $savedFilter->delete();

    expect(SavedFilter::query()->find($savedFilter->id))->toBeNull()
        ->and(SavedFilter::withTrashed()->find($savedFilter->id))->not->toBeNull();
});
