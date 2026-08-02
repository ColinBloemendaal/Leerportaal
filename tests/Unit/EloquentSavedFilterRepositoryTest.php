<?php

declare(strict_types=1);

use App\Enums\FilterableResource;
use App\Models\SavedFilter;
use App\Models\User;
use App\Repositories\Eloquent\EloquentSavedFilterRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns only the given user\'s saved filters for the given resource, sorted by name', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    SavedFilter::factory()->create(['user_id' => $user->id, 'resource_type' => FilterableResource::Courses, 'name' => 'Zebra']);
    SavedFilter::factory()->create(['user_id' => $user->id, 'resource_type' => FilterableResource::Courses, 'name' => 'Apple']);
    // Different resource type -- must not appear.
    SavedFilter::factory()->create(['user_id' => $user->id, 'resource_type' => FilterableResource::Users, 'name' => 'Other resource']);
    // Different user -- must not appear.
    SavedFilter::factory()->create(['user_id' => $otherUser->id, 'resource_type' => FilterableResource::Courses, 'name' => 'Not mine']);

    $result = (new EloquentSavedFilterRepository)->forUserAndResource($user->id, FilterableResource::Courses);

    expect($result->pluck('name')->all())->toBe(['Apple', 'Zebra']);
});

it('finds a saved filter owned by the user, and not one owned by someone else', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $ownFilter = SavedFilter::factory()->create(['user_id' => $user->id]);
    $othersFilter = SavedFilter::factory()->create(['user_id' => $otherUser->id]);

    $repository = new EloquentSavedFilterRepository;

    expect($repository->findOwnById($user->id, $ownFilter->id)?->is($ownFilter))->toBeTrue()
        ->and($repository->findOwnById($user->id, $othersFilter->id))->toBeNull();
});
