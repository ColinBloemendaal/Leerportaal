<?php

declare(strict_types=1);

use App\Actions\Filtering\CreateSavedFilter;
use App\DataTransferObjects\Filtering\CreateSavedFilterData;
use App\Enums\FilterableResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('creates a saved filter for the user', function (): void {
    $user = User::factory()->create();

    $savedFilter = app(CreateSavedFilter::class)(new CreateSavedFilterData(
        userId: $user->id,
        resourceType: FilterableResource::Courses,
        name: 'Published this month',
        filters: ['status' => 'published'],
    ));

    expect($savedFilter->user_id)->toBe($user->id)
        ->and($savedFilter->resource_type)->toBe(FilterableResource::Courses)
        ->and($savedFilter->name)->toBe('Published this month')
        ->and($savedFilter->filters)->toBe(['status' => 'published']);
});
