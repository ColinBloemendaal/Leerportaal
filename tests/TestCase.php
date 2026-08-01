<?php

declare(strict_types=1);

namespace Tests;

use App\Contracts\Billing\PaymentGateway;
use App\Contracts\Dns\DnsResolver;
use App\Contracts\Ploi\PloiClient;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Fakes\FakeDnsResolver;
use Tests\Fakes\FakePaymentGateway;
use Tests\Fakes\FakePloiClient;

abstract class TestCase extends BaseTestCase
{
    /**
     * Every external-service interface is bound to its fake by default, so
     * no test can accidentally reach a live external service -- individual
     * tests can still override these bindings when they need to assert
     * against a specific fake instance. See CLAUDE.md §8.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(PaymentGateway::class, FakePaymentGateway::class);
        $this->app->bind(DnsResolver::class, FakeDnsResolver::class);
        $this->app->bind(PloiClient::class, FakePloiClient::class);

        // CI's php job never runs `npm run build`, so no manifest exists.
        $this->withoutVite();
    }
}
