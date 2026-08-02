<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\FileUploadQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function fileUploadQuestion(): FileUploadQuestion
{
    return new FileUploadQuestion;
}

it('reports its key, label, and that it is not auto-gradable', function (): void {
    expect(FileUploadQuestion::key())->toBe(QuestionTypeEnum::FileUpload)
        ->and(fileUploadQuestion()->isAutoGradable())->toBeFalse();
});

it('passes an empty payload (no constraints)', function (): void {
    $validator = Validator::make([], fileUploadQuestion()->payloadRules());

    expect($validator->passes())->toBeTrue();
});

it('passes a payload with mime type and size constraints', function (): void {
    $validator = Validator::make(
        ['allowed_mime_types' => ['application/pdf'], 'max_size_bytes' => 5_000_000],
        fileUploadQuestion()->payloadRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('always reports pending manual grading, regardless of the answer', function (): void {
    $question = Question::factory()->create(['points' => 10]);

    expect(fileUploadQuestion()->grade($question, 'media-id-123')->requiresManualGrading)->toBeTrue()
        ->and(fileUploadQuestion()->grade($question, null)->requiresManualGrading)->toBeTrue();
});

it('has no correctness verdict until manually graded', function (): void {
    $question = Question::factory()->create(['points' => 10]);

    expect(fileUploadQuestion()->grade($question, 'media-id-123')->isCorrect)->toBeNull();
});
