<?php

declare(strict_types=1);

namespace App\Contracts\Billing;

/**
 * Contract shape locked in during Phase 0; Phase 8 builds the real
 * MollieGateway implementation and normalized webhook handler -- see
 * CLAUDE.md §8 (Billing).
 */
interface PaymentGateway
{
    /**
     * @return array<string, mixed> gateway-specific payment reference and metadata
     */
    public function createPayment(int $amountCents, string $description, string $redirectUrl): array;

    public function getPaymentStatus(string $paymentId): string;
}
