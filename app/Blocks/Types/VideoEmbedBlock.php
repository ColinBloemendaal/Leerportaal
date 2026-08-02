<?php

declare(strict_types=1);

namespace App\Blocks\Types;

use App\Blocks\Contracts\BlockType;

/**
 * External hosting only (Vimeo/Mux/YouTube unlisted) -- no self-hosted
 * video, per CLAUDE.md §3. `url` is the embed URL; `provider` is a
 * free-form hint for the player component, not validated against a
 * fixed list, since the actual provider set is a product decision for
 * whoever builds the player UI, not this schema-level type definition.
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
            'url' => ['required', 'string', 'url', 'max:2048'],
            'provider' => ['nullable', 'string', 'max:50'],
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
