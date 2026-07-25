<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::middleware('web')->get('/__test-tenant-context', fn (TenantContext $context) => [
        'resolved' => $context->check(),
        'reseller_id' => $context->id(),
    ]);
});

it('resolves the tenant from a valid reseller_slug cookie', function (): void {
    $reseller = Reseller::factory()->create(['slug' => 'acme']);

    $response = $this->withCookie('reseller_slug', 'acme')->get('/__test-tenant-context');

    $response->assertJson(['resolved' => true, 'reseller_id' => $reseller->id]);
});

it('leaves the tenant unresolved with no cookie', function (): void {
    $response = $this->get('/__test-tenant-context');

    $response->assertJson(['resolved' => false, 'reseller_id' => null]);
});

it('leaves the tenant unresolved for an unknown reseller_slug cookie', function (): void {
    $response = $this->withCookie('reseller_slug', 'does-not-exist')->get('/__test-tenant-context');

    $response->assertJson(['resolved' => false, 'reseller_id' => null]);
});

it('leaves the tenant unresolved for a suspended reseller', function (): void {
    Reseller::factory()->suspended()->create(['slug' => 'suspended-co']);

    $response = $this->withCookie('reseller_slug', 'suspended-co')->get('/__test-tenant-context');

    $response->assertJson(['resolved' => false, 'reseller_id' => null]);
});
