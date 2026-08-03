<?php

declare(strict_types=1);

use App\Services\Billing\MollieGateway;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('creates a payment and returns its id, checkout URL, and status', function (): void {
    Http::fake(['api.mollie.com/*' => Http::response([
        'id' => 'tr_abc123',
        'status' => 'open',
        '_links' => ['checkout' => ['href' => 'https://mollie.test/pay/tr_abc123']],
    ], 201)]);

    $gateway = new MollieGateway(app(HttpFactory::class), 'test-api-key');
    $payment = $gateway->createPayment(1500, 'Course assignment', 'https://example.test/return');

    expect($payment)->toBe([
        'id' => 'tr_abc123',
        'checkoutUrl' => 'https://mollie.test/pay/tr_abc123',
        'status' => 'open',
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://api.mollie.com/v2/payments'
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer test-api-key')
            && $request['amount']['value'] === '15.00'
            && $request['amount']['currency'] === 'EUR'
            && $request['description'] === 'Course assignment'
            && $request['redirectUrl'] === 'https://example.test/return';
    });
});

it('throws when payment creation fails', function (): void {
    Http::fake(['api.mollie.com/*' => Http::response(['detail' => 'nope'], 422)]);

    $gateway = new MollieGateway(app(HttpFactory::class), 'test-api-key');

    expect(fn () => $gateway->createPayment(1500, 'Course assignment', 'https://example.test/return'))
        ->toThrow(RuntimeException::class);
});

it('fetches a payment status by id', function (): void {
    Http::fake(['api.mollie.com/v2/payments/tr_abc123' => Http::response(['id' => 'tr_abc123', 'status' => 'paid'], 200)]);

    $gateway = new MollieGateway(app(HttpFactory::class), 'test-api-key');

    expect($gateway->getPaymentStatus('tr_abc123'))->toBe('paid');

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://api.mollie.com/v2/payments/tr_abc123'
            && $request->method() === 'GET'
            && $request->hasHeader('Authorization', 'Bearer test-api-key');
    });
});

it('throws when the payment status lookup fails', function (): void {
    Http::fake(['api.mollie.com/*' => Http::response(['detail' => 'not found'], 404)]);

    $gateway = new MollieGateway(app(HttpFactory::class), 'test-api-key');

    expect(fn () => $gateway->getPaymentStatus('does-not-exist'))->toThrow(RuntimeException::class);
});
