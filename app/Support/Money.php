<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Integer cents, never float -- see CLAUDE.md §4. Deliberately minimal
 * for now (no currency, no rounding/allocation helpers): the first
 * consumers are plain storage fields (Course price columns). Billing
 * calculations (Phase 6) can extend this when they need it, rather than
 * building arithmetic ahead of a concrete caller.
 */
final readonly class Money
{
    private function __construct(public int $cents) {}

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }
}
