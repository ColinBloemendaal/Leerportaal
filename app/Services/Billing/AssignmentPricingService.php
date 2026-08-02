<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\Course;
use App\Support\Money;

/**
 * Implements CLAUDE.md §11's pricing rules exactly:
 * - Reseller-authored course: max(15% x reseller_set_price, EUR 3.00).
 * - Catalog course: the fixed platform_price_cents set by a platform admin.
 * - A platform admin's manual price_floor_override_cents, when set,
 *   raises either result further -- it's a floor, never a replacement,
 *   and it's a human's contractual-clause enforcement, never
 *   auto-detected (§11 is explicit about that), so this service just
 *   applies whatever value is already on the course.
 */
final readonly class AssignmentPricingService
{
    private const RESELLER_PERCENTAGE = 0.15;

    private const RESELLER_FLOOR_CENTS = 300;

    public function priceFor(Course $course): Money
    {
        $cents = $course->reseller_id === null
            ? $this->catalogPriceCents($course)
            : $this->resellerPriceCents($course);

        if ($course->price_floor_override_cents !== null) {
            $cents = max($cents, $course->price_floor_override_cents->cents);
        }

        return Money::fromCents($cents);
    }

    private function catalogPriceCents(Course $course): int
    {
        return $course->platform_price_cents?->cents ?? 0;
    }

    private function resellerPriceCents(Course $course): int
    {
        $setPrice = $course->reseller_set_price_cents?->cents ?? 0;
        $percentage = (int) round($setPrice * self::RESELLER_PERCENTAGE);

        return max($percentage, self::RESELLER_FLOOR_CENTS);
    }
}
