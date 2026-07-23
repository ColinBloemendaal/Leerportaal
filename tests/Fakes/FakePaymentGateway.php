<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Contracts\Billing\PaymentGateway;

final class FakePaymentGateway implements PaymentGateway
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $payments = [];

    public function createPayment(int $amountCents, string $description, string $redirectUrl): array
    {
        $id = uniqid('fake_payment_', more_entropy: true);

        return $this->payments[$id] = [
            'id' => $id,
            'amountCents' => $amountCents,
            'description' => $description,
            'redirectUrl' => $redirectUrl,
            'status' => 'open',
        ];
    }

    public function getPaymentStatus(string $paymentId): string
    {
        return $this->payments[$paymentId]['status'] ?? 'unknown';
    }

    public function markAsPaid(string $paymentId): void
    {
        if (isset($this->payments[$paymentId])) {
            $this->payments[$paymentId]['status'] = 'paid';
        }
    }

    public function markAsFailed(string $paymentId): void
    {
        if (isset($this->payments[$paymentId])) {
            $this->payments[$paymentId]['status'] = 'failed';
        }
    }
}
