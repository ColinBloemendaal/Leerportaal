<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Group;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
final class GroupFactory extends Factory
{
    protected $model = Group::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // reseller_id and resellerklant_id default independently, same
        // as every other factory in this codebase -- a plain create()
        // may pair them with unrelated resellers, but nothing currently
        // depends on that consistency, and eagerly deriving one from the
        // other here would break Factory::for() overrides (it did,
        // during testing: for()'s associated FK got silently
        // clobbered by this array on every create()).
        return [
            'reseller_id' => Reseller::factory(),
            'resellerklant_id' => ResellerKlant::factory(),
            'name' => fake()->words(2, true),
        ];
    }
}
