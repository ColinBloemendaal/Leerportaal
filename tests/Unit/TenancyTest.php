<?php

declare(strict_types=1);

use App\Concerns\TenantScoped;
use App\Models\Reseller;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function tenancyFixtureModel(): Model
{
    return new class extends Model
    {
        use TenantScoped;

        protected $table = 'tenancy_fixtures';

        protected $guarded = [];
    };
}

beforeEach(function (): void {
    Schema::create('tenancy_fixtures', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('reseller_id');
        $table->string('name');
        $table->timestamps();
    });
});

afterEach(function (): void {
    Schema::dropIfExists('tenancy_fixtures');
});

it('reports no tenant by default', function (): void {
    $context = app(TenantContext::class);

    expect($context->check())->toBeFalse()
        ->and($context->id())->toBeNull()
        ->and($context->get())->toBeNull();
});

it('reports the set tenant', function (): void {
    $reseller = Reseller::factory()->create();
    $context = app(TenantContext::class);
    $context->set($reseller);

    expect($context->check())->toBeTrue()
        ->and($context->id())->toBe($reseller->id)
        ->and($context->get()->is($reseller))->toBeTrue();
});

it('keeps the spatie/laravel-permission team id in sync when the tenant is set', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    expect(app(PermissionRegistrar::class)->getPermissionsTeamId())->toBe($reseller->id);
});

it('fails closed when no tenant is set', function (): void {
    $model = tenancyFixtureModel();
    $model::query()->insert(['reseller_id' => 1, 'name' => 'row for reseller 1']);

    expect($model::all())->toHaveCount(0);
});

it('only returns rows for the current tenant', function (): void {
    $model = tenancyFixtureModel();
    $model::query()->insert(['reseller_id' => 1, 'name' => 'reseller 1 row']);
    $model::query()->insert(['reseller_id' => 2, 'name' => 'reseller 2 row']);

    $resellerOne = Reseller::factory()->create(['id' => 1]);
    app(TenantContext::class)->set($resellerOne);

    $visible = $model::all();

    expect($visible)->toHaveCount(1)
        ->and($visible->first()->name)->toBe('reseller 1 row');
});

it('stamps reseller_id from the tenant context on create', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $model = tenancyFixtureModel();
    $created = $model::create(['name' => 'stamped']);

    expect($created->reseller_id)->toBe($reseller->id);
});

it('does not override an explicitly set reseller_id on create', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();
    app(TenantContext::class)->set($resellerA);

    $model = tenancyFixtureModel();
    $created = $model::create(['name' => 'explicit', 'reseller_id' => $resellerB->id]);

    expect($created->reseller_id)->toBe($resellerB->id);
});

it('exposes all rows via withoutTenantScope', function (): void {
    $model = tenancyFixtureModel();
    $model::query()->insert(['reseller_id' => 1, 'name' => 'reseller 1 row']);
    $model::query()->insert(['reseller_id' => 2, 'name' => 'reseller 2 row']);

    $resellerOne = Reseller::factory()->create(['id' => 1]);
    app(TenantContext::class)->set($resellerOne);

    // Admin/platform context: auditing across all resellers.
    $all = $model::query()->withoutTenantScope()->get();

    expect($all)->toHaveCount(2);
});
