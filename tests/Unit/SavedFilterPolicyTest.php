<?php

declare(strict_types=1);

use App\Models\SavedFilter;
use App\Models\User;
use App\Policies\SavedFilterPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new SavedFilterPolicy;
});

it('lets any authenticated user view and create saved filters', function (): void {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue();
});

it('lets the owner delete their own saved filter', function (): void {
    $user = User::factory()->create();
    $savedFilter = SavedFilter::factory()->create(['user_id' => $user->id]);

    expect($this->policy->delete($user, $savedFilter))->toBeTrue();
});

it('denies deleting a saved filter that belongs to someone else', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $savedFilter = SavedFilter::factory()->create(['user_id' => $otherUser->id]);

    expect($this->policy->delete($user, $savedFilter))->toBeFalse();
});
