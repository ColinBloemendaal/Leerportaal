<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Contracts\Billing\PaymentGateway;
use Illuminate\Http\Client\Factory as HttpFactory;
use RuntimeException;

/**
 * Talks to Mollie's REST API directly via Laravel's own HTTP client, the
 * same shape as App\Services\Ploi\HttpPloiClient, rather than pulling in
 * the mollie/mollie-api-php SDK -- keeps this trivially testable with
 * Http::fake() and avoids a dependency for what is, underneath, a plain
 * JSON REST API.
 */
final class MollieGateway implements PaymentGateway
{
    private const BASE_URL = 'https://api.mollie.com/v2';

    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $apiKey,
    ) {}

    public function createPayment(int $amountCents, string $description, string $redirectUrl): array
    {
        $response = $this->http
            ->withToken($this->apiKey)
            ->asJson()
            ->post(self::BASE_URL.'/payments', [
                'amount' => [
                    'currency' => 'EUR',
                    'value' => number_format($amountCents / 100, 2, '.', ''),
                ],
                'description' => $description,
                'redirectUrl' => $redirectUrl,
            ]);

        if ($response->failed()) {
            throw new RuntimeException("Mollie payment creation failed: {$response->body()}");
        }

        $id = $response->json('id');
        $status = $response->json('status');
        $checkoutUrl = $response->json('_links.checkout.href');

        if (! is_string($id) || ! is_string($status)) {
            throw new RuntimeException('Mollie payment creation returned an unexpected response shape.');
        }

        return [
            'id' => $id,
            'checkoutUrl' => is_string($checkoutUrl) ? $checkoutUrl : null,
            'status' => $status,
        ];
    }

    public function getPaymentStatus(string $paymentId): string
    {
        $response = $this->http
            ->withToken($this->apiKey)
            ->get(self::BASE_URL."/payments/{$paymentId}");

        if ($response->failed()) {
            throw new RuntimeException("Mollie payment lookup failed for {$paymentId}: {$response->body()}");
        }

        $status = $response->json('status');

        if (! is_string($status)) {
            throw new RuntimeException("Mollie payment lookup for {$paymentId} returned an unexpected response shape.");
        }

        return $status;
    }
}
