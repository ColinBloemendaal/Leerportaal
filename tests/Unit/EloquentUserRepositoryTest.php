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
