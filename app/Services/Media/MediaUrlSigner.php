<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Media;
use DateTimeInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Filesystem\FilesystemAdapter;
use RuntimeException;

/**
 * Temporary signed URLs for private media -- CLAUDE.md §3 "private disk
 * + signed URLs". `Illuminate\Contracts\Filesystem\Filesystem` (what
 * `disk()` is typed to return) doesn't declare `temporaryUrl()` -- it
 * only exists on the concrete `FilesystemAdapter` every real driver
 * (local, s3) actually returns. The instanceof check below is what lets
 * this be called safely: a genuine runtime guard, not a cast papering
 * over a type mismatch.
 */
final readonly class MediaUrlSigner
{
    public function __construct(private FilesystemFactory $filesystem) {}

    public function sign(Media $media, DateTimeInterface $expiresAt): string
    {
        $disk = $this->filesystem->disk($media->disk);

        if (! $disk instanceof FilesystemAdapter) {
            throw new RuntimeException("Disk '{$media->disk}' does not support temporary URLs.");
        }

        return $disk->temporaryUrl($media->path, $expiresAt);
    }
}
