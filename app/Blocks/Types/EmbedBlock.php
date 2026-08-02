<?php

declare(strict_types=1);

namespace App\Blocks\Types;

use App\Blocks\Contracts\BlockType;

/**
 * Generic iframe-style embed (a distinct type from VideoEmbedBlock,
 * which is specifically for the external video providers CLAUDE.md §3
 * names) -- e.g. an interactive tool or third-party widget.
 */
final class EmbedBlock implements BlockType
{
    public static function label(): string
    {
        return 'Embed';
    }

    /**
     * @return array<string, mixed>
     */
    public function contentRules(): array
    {
        return [
            'url' => ['required', 'string', 'url', 'max:2048'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Blocks/Editor/EmbedBlock';
    }

    public function playerComponent(): string
    {
        return 'Blocks/Player/EmbedBlock';
    }
}
