<?php

declare(strict_types=1);

use App\Models\CustomDomain;
use App\Models\Reseller;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::middleware('web')->get('/__test-tenant-context', fn (TenantContext $context) => [
        'resolved' => $context->check(),
        'reseller_id' => $context->id(),
    ]);
});

it('resolves the tenant from a verified custom domain', function (): void {
    $reseller = Reseller::factory()->create();
    CustomDomain::factory()->for($reseller, 'reseller')->verified()->create(['domain' => 'localhost']);

    $response = $this->get('/__test-tenant-context');

    $response->assertJson(['resolved' => true, 'reseller_id' => $reseller->id]);
});

it('does not resolve from an unverified custom domain', function (): void {
    $reseller = Reseller::factory()->create();
    CustomDomain::factory()->for($reseller, 'reseller')->create(['domain' => 'localhost']);

    $response = $this->get('/__test-tenant-context');

    $response->assertJson(['resolved' => false, 'reseller_id' => null]);
});

it('prefers the custom domain over the cookie when both resolve', function (): void {
    $domainReseller = Reseller::factory()->create();
    $cookieReseller = Reseller::factory()->create(['slug' => 'cookie-reseller']);
    CustomDomain::factory()->for($domainReseller, 'reseller')->verified()->create(['domain' => 'localhost']);

    $response = $this->withCookie('reseller_slug', 'cookie-reseller')->get('/__test-tenant-context');

    $response->assertJson(['resolved' => true, 'reseller_id' => $domainReseller->id]);
});
