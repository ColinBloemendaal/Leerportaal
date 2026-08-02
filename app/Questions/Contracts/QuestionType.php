<?php

declare(strict_types=1);

namespace App\Questions\Contracts;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;

/**
 * One class per case of App\Enums\QuestionTypeEnum, resolved via
 * App\Questions\QuestionTypeRegistry. The real signature per CLAUDE.md
 * §5, firmed up now that the questions table, GradeResult, and
 * QuestionTypeEnum all exist -- the Phase 0 placeholder that used to
 * live here (label/payloadRules/editorComponent/playerComponent/grade
 * as a loose array/isAutoGradable) is superseded, since nothing
 * implemented it yet.
 */
interface QuestionType
{
    public static function key(): QuestionTypeEnum;

    public static function label(): string;

    /**
     * Validation rules for this type's `payload` JSON, keyed the same
     * way FormRequest::rules() is.
     *
     * @return array<string, mixed>
     */
    public function payloadRules(): array;

    public function editorComponent(): string;

    public function playerComponent(): string;

    public function grade(Question $question, mixed $answer): GradeResult;

    public function isAutoGradable(): bool;
}
