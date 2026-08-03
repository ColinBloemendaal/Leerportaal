<?php

declare(strict_types=1);

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Enums\Role;
use App\Models\Reseller;
use App\Models\User;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('searches across every reseller, including platform staff', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();
    User::factory()->create(['name' => 'Jane Acme', 'reseller_id' => $resellerA->id]);
    User::factory()->create(['name' => 'John Beta', 'reseller_id' => $resellerB->id]);
    User::factory()->platformStaff()->create(['name' => 'Acme Staff', 'platform_role' => Role::PlatformAdmin]);

    $repository = app(EloquentUserRepository::class);
    $filters = new FilterRequestData(search: 'acme', sort: null, sortDirection: 'asc', filters: []);

    $result = $repository->paginate($filters);

    expect(collect($result->items())->pluck('name')->all())->toEqualCanonicalizing(['Jane Acme', 'Acme Staff']);
});

it('filters by reseller_id', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();
    User::factory()->create(['reseller_id' => $resellerA->id]);
    User::factory()->create(['reseller_id' => $resellerB->id]);

    $repository = app(EloquentUserRepository::class);
    $filters = new FilterRequestData(search: null, sort: null, sortDirection: 'asc', filters: ['reseller_id' => (string) $resellerA->id]);

    $result = $repository->paginate($filters);

    expect($result->total())->toBe(1);
});

it('finds a user by id, eager-loading their reseller', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme']);
    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    $found = app(EloquentUserRepository::class)->findById($user->id);

    expect($found?->id)->toBe($user->id)
        ->and($found?->reseller?->name)->toBe('Acme');
});

it('returns null for a non-existent user id', function (): void {
    expect(app(EloquentUserRepository::class)->findById(999999))->toBeNull();
});
