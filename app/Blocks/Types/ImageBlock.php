<?php

declare(strict_types=1);

namespace App\Blocks\Types;

use App\Blocks\Contracts\BlockType;

/**
 * `url` is a plain string for now, not an upload -- the media library
 * (a later Phase 3 task) is what will let authors upload rather than
 * paste a URL; this block's content shape doesn't need to change when
 * that lands, only the editor component's UI does.
 */
final class ImageBlock implements BlockType
{
    public static function label(): string
    {
        return 'Image';
    }

    /**
     * @return array<string, mixed>
     */
    public function contentRules(): array
    {
        return [
            'url' => ['required', 'string', 'url', 'max:2048'],
            'alt' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Blocks/Editor/ImageBlock';
    }

    public function playerComponent(): string
    {
        return 'Blocks/Player/ImageBlock';
    }
}
