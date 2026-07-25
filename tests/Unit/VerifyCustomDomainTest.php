<?php

declare(strict_types=1);

use App\Actions\Tenancy\VerifyCustomDomain;
use App\Enums\CustomDomainStatus;
use App\Models\CustomDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeDnsResolver;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('marks the domain verified when the CNAME matches the configured target', function (): void {
    config(['tenancy.custom_domain_target' => 'tenants.leerportaal.test']);

    $domain = CustomDomain::factory()->create(['domain' => 'lms.acme.test']);

    $dns = new FakeDnsResolver;
    $dns->setCnameTargets('lms.acme.test', ['tenants.leerportaal.test']);

    $result = (new VerifyCustomDomain($dns))($domain);

    expect($result->status)->toBe(CustomDomainStatus::Verified)
        ->and($result->verified_at)->not->toBeNull();
});

it('marks the domain failed when the CNAME does not match', function (): void {
    config(['tenancy.custom_domain_target' => 'tenants.leerportaal.test']);

    $domain = CustomDomain::factory()->create(['domain' => 'lms.acme.test']);

    $dns = new FakeDnsResolver;
    $dns->setCnameTargets('lms.acme.test', ['somewhere-else.test']);

    $result = (new VerifyCustomDomain($dns))($domain);

    expect($result->status)->toBe(CustomDomainStatus::Failed)
        ->and($result->verified_at)->toBeNull();
});

it('marks the domain failed when there is no CNAME record at all', function (): void {
    config(['tenancy.custom_domain_target' => 'tenants.leerportaal.test']);

    $domain = CustomDomain::factory()->create(['domain' => 'lms.acme.test']);

    $result = (new VerifyCustomDomain(new FakeDnsResolver))($domain);

    expect($result->status)->toBe(CustomDomainStatus::Failed);
});
