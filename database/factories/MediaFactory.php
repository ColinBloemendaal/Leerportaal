<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Media;
use App\Models\Reseller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
final class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = fake()->word().'.jpg';

        return [
            'reseller_id' => null,
            'uploaded_by_user_id' => null,
            'disk' => 's3',
            'path' => "media/{$filename}",
            'original_filename' => $filename,
            'mime_type' => 'image/jpeg',
            'size_bytes' => fake()->numberBetween(1_000, 5_000_000),
        ];
    }

    public function forReseller(?int $resellerId = null): self
    {
        return $this->state([
            'reseller_id' => $resellerId ?? Reseller::factory(),
        ]);
    }
}
