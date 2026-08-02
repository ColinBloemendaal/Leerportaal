<?php

declare(strict_types=1);

use App\Enums\VideoProvider;

it('detects vimeo urls', function (): void {
    expect(VideoProvider::fromUrl('https://vimeo.com/123456'))->toBe(VideoProvider::Vimeo);
});

it('detects mux urls', function (): void {
    expect(VideoProvider::fromUrl('https://stream.mux.com/abc123.m3u8'))->toBe(VideoProvider::Mux);
});

it('detects youtube urls including the short domain', function (): void {
    expect(VideoProvider::fromUrl('https://www.youtube.com/watch?v=abc123'))->toBe(VideoProvider::YouTube)
        ->and(VideoProvider::fromUrl('https://youtu.be/abc123'))->toBe(VideoProvider::YouTube);
});

it('returns null for a self-hosted or unrecognized url', function (): void {
    expect(VideoProvider::fromUrl('https://cdn.example.com/videos/lesson-1.mp4'))->toBeNull();
});

it('returns null for a malformed url', function (): void {
    expect(VideoProvider::fromUrl('not-a-url'))->toBeNull();
});
