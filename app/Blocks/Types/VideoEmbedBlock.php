<?php

declare(strict_types=1);

namespace App\Blocks\Types;

use App\Blocks\Contracts\BlockType;
use App\Enums\VideoProvider;
use App\Rules\AllowedVideoProvider;
use Illuminate\Validation\Rule;

/**
 * External hosting only (Vimeo/Mux/YouTube unlisted) -- no self-hosted
 * video, per CLAUDE.md §3. `url` must resolve to one of
 * App\Enums\VideoProvider (enforced by App\Rules\AllowedVideoProvider,
 * not just documented) -- the block's content shape already can't hold
 * an uploaded file, but that alone doesn't stop someone pointing it at
 * an arbitrary self-hosted stream. `provider` is an optional explicit
 * override of the same enum, for when auto-detection from the URL
 * isn't enough (e.g. a Mux playback URL that doesn't obviously say
 * "mux").
 */
final class VideoEmbedBlock implements BlockType
{
    public static function label(): string
    {
        return 'Video embed';
    }

    /**
     * @return array<string, mixed>
     */
    public function contentRules(): array
    {
        return [
            'url' => ['required', 'string', 'url', 'max:2048', new AllowedVideoProvider],
            'provider' => ['nullable', 'string', Rule::enum(VideoProvider::class)],
        ];
    }

    public function editorComponent(): string
    {
        return 'Blocks/Editor/VideoEmbedBlock';
    }

    public function playerComponent(): string
    {
        return 'Blocks/Player/VideoEmbedBlock';
    }
}
