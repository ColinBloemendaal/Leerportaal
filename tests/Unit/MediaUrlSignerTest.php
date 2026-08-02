<?php

declare(strict_types=1);

use App\Models\Media;
use App\Services\Media\MediaUrlSigner;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('delegates to the disk\'s temporary URL generation', function (): void {
    Storage::fake('s3');
    Storage::disk('s3')->buildTemporaryUrlsUsing(
        fn (string $path, DateTimeInterface $expiration): string => "https://signed.example/{$path}?expires={$expiration->getTimestamp()}",
    );

    $media = Media::factory()->create(['disk' => 's3', 'path' => 'reseller-1/handout.pdf']);
    $signer = new MediaUrlSigner(app(FilesystemFactory::class));

    $expiresAt = now()->addMinutes(5);
    $url = $signer->sign($media, $expiresAt);

    expect($url)->toBe("https://signed.example/reseller-1/handout.pdf?expires={$expiresAt->getTimestamp()}");
});
