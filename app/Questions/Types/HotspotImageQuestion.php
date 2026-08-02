<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;

/**
 * `payload`: {
 *   "image_url": string,
 *   "regions": [{"id", "x", "y", "width", "height", "label"}, ...] -- x/y/width/height
 *              are percentages (0-100) of the image, resolution-independent,
 *              "label" is required text (not just visual coordinates) so
 *              every region has a real accessible name,
 *   "correct_region_ids": [string, ...]
 * }
 * `answer` (submitted): list<string> of clicked/selected region ids.
 *
 * Every region requires a text `label` specifically so the player can
 * offer a fully keyboard-operable labeled checkbox list as the actual
 * answer-submission mechanism -- not a bolt-on fallback next to an
 * image click target, the primary one. Same Moodle-style partial credit
 * as multiple_response (kept as its own small copy here rather than a
 * shared helper -- the two algorithms are simple enough that sharing
 * would cost more in indirection than it'd save).
 */
final class HotspotImageQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::HotspotImage;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::HotspotImage->label();
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadRules(): array
    {
        return [
            'image_url' => ['required', 'string', 'url'],
            'regions' => ['required', 'array', 'min:2'],
            'regions.*.id' => ['required', 'string'],
            'regions.*.x' => ['required', 'numeric', 'min:0', 'max:100'],
            'regions.*.y' => ['required', 'numeric', 'min:0', 'max:100'],
            'regions.*.width' => ['required', 'numeric', 'min:0', 'max:100'],
            'regions.*.height' => ['required', 'numeric', 'min:0', 'max:100'],
            'regions.*.label' => ['required', 'string'],
            'correct_region_ids' => ['required', 'array', 'min:1'],
            'correct_region_ids.*' => ['required', 'string'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/HotspotImageQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/HotspotImageQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        $points = (float) $question->points;

        if (! is_array($answer)) {
            return GradeResult::incorrect($points);
        }

        /** @var list<string> $correctRegionIds */
        $correctRegionIds = $question->payload['correct_region_ids'] ?? [];
        $selected = array_values(array_unique(array_filter($answer, 'is_string')));

        if ($correctRegionIds === []) {
            return GradeResult::incorrect($points);
        }

        if ($this->sameSet($selected, $correctRegionIds)) {
            return GradeResult::correct($points);
        }

        $pointsPerRegion = $points / count($correctRegionIds);
        $score = 0.0;

        foreach ($selected as $regionId) {
            $score += in_array($regionId, $correctRegionIds, true) ? $pointsPerRegion : -$pointsPerRegion;
        }

        $score = max(0.0, min($points, $score));

        return $score > 0.0
            ? GradeResult::partial($score, $points)
            : GradeResult::incorrect($points);
    }

    public function isAutoGradable(): bool
    {
        return true;
    }

    /**
     * @param  list<string>  $a
     * @param  list<string>  $b
     */
    private function sameSet(array $a, array $b): bool
    {
        sort($a);
        sort($b);

        return $a === $b;
    }
}
