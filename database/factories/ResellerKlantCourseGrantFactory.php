<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\ResellerKlantCourseGrant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResellerKlantCourseGrant>
 */
final class ResellerKlantCourseGrantFactory extends Factory
{
    protected $model = ResellerKlantCourseGrant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // reseller_id and resellerklant_id default independently, same
        // as GroupFactory -- see that factory's comment for why deriving
        // one from the other here would break Factory::for() overrides.
        return [
            'reseller_id' => Reseller::factory(),
            'resellerklant_id' => ResellerKlant::factory(),
            'course_id' => Course::factory(),
            'granted_by_user_id' => null,
            'granted_at' => now(),
            'revoked_at' => null,
        ];
    }

    public function revoked(): self
    {
        return $this->state(fn (): array => ['revoked_at' => now()]);
    }
}
