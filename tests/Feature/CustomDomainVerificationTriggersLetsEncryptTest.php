<?php

declare(strict_types=1);

use App\Actions\Tenancy\VerifyCustomDomain;
use App\Contracts\Ploi\PloiClient;
use App\Models\CustomDomain;
use Tests\Fakes\FakeDnsResolver;
use Tests\Fakes\FakePloiClient;

it('requests a Lets Encrypt certificate automatically once a domain verifies', function (): void {
    config(['tenancy.custom_domain_target' => 'tenants.leerportaal.test']);

    $ploi = new FakePloiClient;
    $this->app->instance(PloiClient::class, $ploi);

    $domain = CustomDomain::factory()->create(['domain' => 'lms.acme.test']);

    $dns = new FakeDnsResolver;
    $dns->setCnameTargets('lms.acme.test', ['tenants.leerportaal.test']);

    (new VerifyCustomDomain($dns))($domain);

    expect($ploi->requestedCertificateFor('lms.acme.test'))->toBeTrue();
});

it('does not request a certificate when verification fails', function (): void {
    config(['tenancy.custom_domain_target' => 'tenants.leerportaal.test']);

    $ploi = new FakePloiClient;
    $this->app->instance(PloiClient::class, $ploi);

    $domain = CustomDomain::factory()->create(['domain' => 'lms.acme.test']);

    (new VerifyCustomDomain(new FakeDnsResolver))($domain);

    expect($ploi->requestedCertificates())->toBe([]);
});
