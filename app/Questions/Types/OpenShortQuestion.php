<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;
use Illuminate\Validation\Rule;

/**
 * `payload`: {
 *   "match_mode": "exact"|"contains"|"regex",
 *   "case_sensitive": bool,
 *   "acceptable_answers": [string, ...] -- literal strings for exact/contains,
 *                                          regex patterns for regex mode
 * }
 * `answer` (submitted): string.
 */
final class OpenShortQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::OpenShort;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::OpenShort->label();
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadRules(): array
    {
        return [
            'match_mode' => ['required', Rule::in(['exact', 'contains', 'regex'])],
            'case_sensitive' => ['sometimes', 'boolean'],
            'acceptable_answers' => ['required', 'array', 'min:1'],
            'acceptable_answers.*' => ['required', 'string'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/OpenShortQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/OpenShortQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        $points = (float) $question->points;

        if (! is_string($answer) || trim($answer) === '') {
            return GradeResult::incorrect($points);
        }

        $payload = $question->payload ?? [];
        $matchMode = $payload['match_mode'] ?? 'exact';
        $caseSensitive = (bool) ($payload['case_sensitive'] ?? false);
        /** @var list<string> $acceptableAnswers */
        $acceptableAnswers = $payload['acceptable_answers'] ?? [];

        $submitted = trim($answer);
        $matches = array_any(
            $acceptableAnswers,
            fn (string $candidate): bool => $this->matches($matchMode, $submitted, $candidate, $caseSensitive),
        );

        return $matches ? GradeResult::correct($points) : GradeResult::incorrect($points);
    }

    public function isAutoGradable(): bool
    {
        return true;
    }

    private function matches(string $mode, string $submitted, string $candidate, bool $caseSensitive): bool
    {
        return match ($mode) {
            'contains' => $caseSensitive
                ? str_contains($submitted, $candidate)
                : str_contains(mb_strtolower($submitted), mb_strtolower($candidate)),
            'regex' => $this->matchesRegex($submitted, $candidate, $caseSensitive),
            default => $caseSensitive
                ? $submitted === $candidate
                : mb_strtolower($submitted) === mb_strtolower($candidate),
        };
    }

    /**
     * A malformed admin-authored pattern must never crash grading --
     * suppressing the warning here (rather than letting PHP surface it)
     * is a deliberate, narrow exception, not a stand-in for validating
     * the pattern up front.
     */
    private function matchesRegex(string $submitted, string $pattern, bool $caseSensitive): bool
    {
        $delimited = $caseSensitive ? $pattern : $pattern.'i';

        $result = @preg_match($delimited, $submitted);

        return $result === 1;
    }
}
