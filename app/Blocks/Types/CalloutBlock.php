<?php

declare(strict_types=1);

namespace App\Blocks\Types;

use App\Blocks\Contracts\BlockType;

final class CalloutBlock implements BlockType
{
    /**
     * @var list<string>
     */
    private const VARIANTS = ['info', 'warning', 'danger', 'success'];

    public static function label(): string
    {
        return 'Callout';
    }

    /**
     * @return array<string, mixed>
     */
    public function contentRules(): array
    {
        return [
            'text' => ['required', 'string', 'max:1000'],
            'variant' => ['required', 'string', 'in:'.implode(',', self::VARIANTS)],
        ];
    }

    public function editorComponent(): string
    {
        return 'Blocks/Editor/CalloutBlock';
    }

    public function playerComponent(): string
    {
        return 'Blocks/Player/CalloutBlock';
    }
}
