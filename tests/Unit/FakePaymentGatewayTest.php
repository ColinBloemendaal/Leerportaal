<?php

declare(strict_types=1);

use Tests\Fakes\FakePaymentGateway;

it('tracks a created payment as open', function (): void {
    $gateway = new FakePaymentGateway;

    $payment = $gateway->createPayment(1500, 'Course assignment', 'https://example.test/return');

    expect($gateway->getPaymentStatus($payment['id']))->toBe('open');
});

it('can be marked as paid or failed', function (): void {
    $gateway = new FakePaymentGateway;
    $payment = $gateway->createPayment(1500, 'Course assignment', 'https://example.test/return');

    $gateway->markAsPaid($payment['id']);
    expect($gateway->getPaymentStatus($payment['id']))->toBe('paid');

    $other = $gateway->createPayment(500, 'Another', 'https://example.test/return');
    $gateway->markAsFailed($other['id']);
    expect($gateway->getPaymentStatus($other['id']))->toBe('failed');
});

it('reports unknown for an unrecognized payment id', function (): void {
    $gateway = new FakePaymentGateway;

    expect($gateway->getPaymentStatus('does-not-exist'))->toBe('unknown');
});
