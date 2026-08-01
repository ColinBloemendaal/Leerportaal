<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MailTemplateType;
use App\Models\Reseller;
use App\Models\ResellerMailTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResellerMailTemplate>
 */
final class ResellerMailTemplateFactory extends Factory
{
    protected $model = ResellerMailTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reseller_id' => Reseller::factory(),
            'type' => MailTemplateType::UserInvited,
            'subject' => fake()->sentence(),
            'body_markdown' => '# '.fake()->sentence().\PHP_EOL.\PHP_EOL.fake()->paragraph(),
        ];
    }
}
