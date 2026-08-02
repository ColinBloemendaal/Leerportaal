<?php

declare(strict_types=1);

namespace App\Blocks\Types;

use App\Blocks\Contracts\BlockType;
use App\Rules\RejectsUnsafeMarkup;

final class RichTextBlock implements BlockType
{
    public static function label(): string
    {
        return 'Rich text';
    }

    /**
     * @return array<string, mixed>
     */
    public function contentRules(): array
    {
        return [
            'html' => ['required', 'string', 'max:50000', new RejectsUnsafeMarkup],
        ];
    }

    public function editorComponent(): string
    {
        return 'Blocks/Editor/RichTextBlock';
    }

    public function playerComponent(): string
    {
        return 'Blocks/Player/RichTextBlock';
    }
}
