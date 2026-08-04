<?php

declare(strict_types=1);

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Enums\Role;
use App\Models\Reseller;
use App\Models\ResellerKlant;
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

it('lists only platform staff with the super-admin role', function (): void {
    $superAdmin = User::factory()->platformStaff()->create(['platform_role' => Role::SuperAdmin]);
    User::factory()->platformStaff()->create(['platform_role' => Role::PlatformAdmin]);
    User::factory()->create();

    $superAdmins = app(EloquentUserRepository::class)->superAdmins();

    expect($superAdmins->pluck('id')->all())->toBe([$superAdmin->id]);
});

it('lists only cursisten for one reseller, excluding reseller staff and other resellers', function (): void {
    $reseller = Reseller::factory()->create();
    $klant = ResellerKlant::factory()->for($reseller)->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id, 'name' => 'Jane Cursist']);
    User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => null]); // reseller staff, no klant
    User::factory()->create(); // a different reseller entirely

    $found = app(EloquentUserRepository::class)->cursistenForReseller($reseller->id);

    expect($found->pluck('id')->all())->toBe([$cursist->id]);
});
