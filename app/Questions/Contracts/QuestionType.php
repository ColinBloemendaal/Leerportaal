<?php

declare(strict_types=1);

namespace App\Questions\Contracts;

/**
 * Contract shape locked in during Phase 0; Phase 4 firms up the real
 * signature (Question model, GradeResult, QuestionTypeEnum) once the
 * questions table and grading pipeline exist -- see CLAUDE.md §5.
 */
interface QuestionType
{
    public static function label(): string;

    /**
     * @return array<string, mixed>
     */
    public function payloadRules(): array;

    public function editorComponent(): string;

    public function playerComponent(): string;

    /**
     * @return array<string, mixed>
     */
    public function grade(mixed $payload, mixed $answer): array;

    public function isAutoGradable(): bool;
}
