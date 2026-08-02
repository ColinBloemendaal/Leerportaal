<?php

declare(strict_types=1);

use App\Actions\Media\UploadMedia;
use App\DataTransferObjects\Media\UploadMediaData;
use App\Models\Reseller;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function uploadMediaAction(): UploadMedia
{
    return new UploadMedia(app(FilesystemFactory::class));
}

it('stores an uploaded file under a platform prefix when no reseller is given', function (): void {
    Storage::fake('s3');
    $user = User::factory()->create();

    $media = (uploadMediaAction())(new UploadMediaData(
        file: UploadedFile::fake()->create('handout.pdf', 100, 'application/pdf'),
        resellerId: null,
        uploadedByUserId: $user->id,
    ));

    expect($media->reseller_id)->toBeNull()
        ->and($media->path)->toStartWith('platform/')
        ->and($media->original_filename)->toBe('handout.pdf')
        ->and($media->mime_type)->toBe('application/pdf')
        ->and($media->size_bytes)->toBeGreaterThan(0)
        ->and($media->uploaded_by_user_id)->toBe($user->id);

    Storage::disk('s3')->assertExists($media->path);
});

it('stores an uploaded file under that reseller\'s own prefix', function (): void {
    Storage::fake('s3');
    $reseller = Reseller::factory()->create();

    $media = (uploadMediaAction())(new UploadMediaData(
        file: UploadedFile::fake()->create('image.jpg', 50, 'image/jpeg'),
        resellerId: $reseller->id,
        uploadedByUserId: null,
    ));

    expect($media->reseller_id)->toBe($reseller->id)
        ->and($media->path)->toStartWith("reseller-{$reseller->id}/");
});
