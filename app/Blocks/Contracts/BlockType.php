<?php

declare(strict_types=1);

namespace App\Blocks\Contracts;

/**
 * One class per case of App\Enums\BlockTypeEnum, resolved via
 * App\Blocks\BlockTypeRegistry. Mirrors App\Questions\Contracts\QuestionType
 * (CLAUDE.md §5) -- the same "one enum case + one PHP class + two Vue
 * components" shape, without the grading concern questions have.
 */
interface BlockType
{
    public static function label(): string;

    /**
     * Validation rules for this type's `content` JSON, keyed the same
     * way FormRequest::rules() is.
     *
     * @return array<string, mixed>
     */
    public function contentRules(): array;

    public function editorComponent(): string;

    public function playerComponent(): string;
}
