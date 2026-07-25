<?php

declare(strict_types=1);

use App\Actions\Tenancy\IssueLetsEncryptCertificate;
use App\Models\CustomDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakePloiClient;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('requests a certificate for a verified domain', function (): void {
    $domain = CustomDomain::factory()->verified()->create(['domain' => 'lms.acme.test']);

    $ploi = new FakePloiClient;
    (new IssueLetsEncryptCertificate($ploi))($domain);

    expect($ploi->requestedCertificateFor('lms.acme.test'))->toBeTrue();
});

it('refuses to request a certificate for an unverified domain', function (): void {
    $domain = CustomDomain::factory()->create(['domain' => 'lms.acme.test']);

    $ploi = new FakePloiClient;

    expect(fn () => (new IssueLetsEncryptCertificate($ploi))($domain))
        ->toThrow(RuntimeException::class);

    expect($ploi->requestedCertificates())->toBe([]);
});
