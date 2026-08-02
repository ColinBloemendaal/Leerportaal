<?php

declare(strict_types=1);

namespace App\Blocks\Types;

use App\Blocks\Contracts\BlockType;

/**
 * A visual separator only -- no content fields at all.
 */
final class DividerBlock implements BlockType
{
    public static function label(): string
    {
        return 'Divider';
    }

    /**
     * @return array<string, mixed>
     */
    public function contentRules(): array
    {
        return [];
    }

    public function editorComponent(): string
    {
        return 'Blocks/Editor/DividerBlock';
    }

    public function playerComponent(): string
    {
        return 'Blocks/Player/DividerBlock';
    }
}
