<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Media;

use Illuminate\Http\UploadedFile;

final readonly class UploadMediaData
{
    public function __construct(
        public UploadedFile $file,
        public ?int $resellerId,
        public ?int $uploadedByUserId,
    ) {}
}
