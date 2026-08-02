<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\DataTransferObjects\Media\UploadMediaData;
use App\Models\Media;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use RuntimeException;

/**
 * "resellerId null" stores under a platform prefix (used inside catalog
 * courses); a reseller ID stores under that reseller's own prefix --
 * same mixed-ownership shape as the rest of this phase.
 */
final readonly class UploadMedia
{
    private const DISK = 's3';

    public function __construct(private FilesystemFactory $filesystem) {}

    public function __invoke(UploadMediaData $data): Media
    {
        $prefix = $data->resellerId !== null ? "reseller-{$data->resellerId}" : 'platform';
        $path = $this->filesystem->disk(self::DISK)->putFile($prefix, $data->file);

        if ($path === false) {
            throw new RuntimeException('Failed to store uploaded media.');
        }

        return Media::create([
            'reseller_id' => $data->resellerId,
            'uploaded_by_user_id' => $data->uploadedByUserId,
            'disk' => self::DISK,
            'path' => $path,
            'original_filename' => $data->file->getClientOriginalName(),
            'mime_type' => $data->file->getMimeType(),
            'size_bytes' => $data->file->getSize(),
        ]);
    }
}
