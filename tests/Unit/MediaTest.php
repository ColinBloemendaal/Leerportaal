<?php

declare(strict_types=1);

use App\Models\Media;
use App\Models\Reseller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('defaults to platform media with no reseller', function (): void {
    $media = Media::factory()->create();

    expect($media->reseller_id)->toBeNull()
        ->and($media->reseller)->toBeNull();
});

it('belongs to a reseller when owned by one', function (): void {
    $reseller = Reseller::factory()->create();
    $media = Media::factory()->forReseller($reseller->id)->create();

    expect($media->reseller)->toBeInstanceOf(Reseller::class)
        ->and($media->reseller->id)->toBe($reseller->id);
});

it('belongs to the uploading user', function (): void {
    $user = User::factory()->create();
    $media = Media::factory()->create(['uploaded_by_user_id' => $user->id]);

    expect($media->uploadedBy?->id)->toBe($user->id);
});

it('soft deletes', function (): void {
    $media = Media::factory()->create();

    $media->delete();

    expect(Media::find($media->id))->toBeNull()
        ->and(Media::withTrashed()->find($media->id))->not->toBeNull();
});
