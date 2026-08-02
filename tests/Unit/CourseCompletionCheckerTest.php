<?php

declare(strict_types=1);

use App\Actions\Progress\MarkBlockCompleted;
use App\DataTransferObjects\Courses\CourseCompletionRuleData;
use App\Enums\CourseCompletionRuleType;
use App\Models\Block;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\Progress\CourseCompletionChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function completionChecker(): CourseCompletionChecker
{
    return app(CourseCompletionChecker::class);
}

it('is incomplete under the default all_lessons rule until every block is done', function (): void {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    $block = Block::factory()->for($lesson)->create();
    $assignment = CourseAssignment::factory()->create();

    expect(completionChecker()->isComplete($assignment, $course))->toBeFalse();

    app(MarkBlockCompleted::class)($assignment, $block);

    expect(completionChecker()->isComplete($assignment, $course))->toBeTrue();
});

it('is complete under a pass_exam rule only once that exam is passed', function (): void {
    $quiz = Quiz::factory()->exam()->create();
    $course = Course::factory()->create([
        'completion_rule' => new CourseCompletionRuleData(CourseCompletionRuleType::PassExam, examQuizId: $quiz->id),
    ]);
    $assignment = CourseAssignment::factory()->create();

    QuizAttempt::factory()->for($quiz)->for($assignment->user, 'user')->submitted()->create([
        'attempt_number' => 1, 'score' => 5, 'max_score' => 10, 'passed' => false,
    ]);

    expect(completionChecker()->isComplete($assignment, $course))->toBeFalse();

    QuizAttempt::factory()->for($quiz)->for($assignment->user, 'user')->submitted()->create([
        'attempt_number' => 2, 'score' => 9, 'max_score' => 10, 'passed' => true,
    ]);

    expect(completionChecker()->isComplete($assignment, $course))->toBeTrue();
});

it('is complete under a minimum_score rule once the threshold is met by the best attempt', function (): void {
    $quiz = Quiz::factory()->exam()->create();
    $course = Course::factory()->create([
        'completion_rule' => new CourseCompletionRuleData(
            CourseCompletionRuleType::MinimumScore,
            examQuizId: $quiz->id,
            minimumScorePercent: 80,
        ),
    ]);
    $assignment = CourseAssignment::factory()->create();

    QuizAttempt::factory()->for($quiz)->for($assignment->user, 'user')->submitted()->create([
        'attempt_number' => 1, 'score' => 6, 'max_score' => 10,
    ]);

    expect(completionChecker()->isComplete($assignment, $course))->toBeFalse();

    QuizAttempt::factory()->for($quiz)->for($assignment->user, 'user')->submitted()->create([
        'attempt_number' => 2, 'score' => 8, 'max_score' => 10,
    ]);

    expect(completionChecker()->isComplete($assignment, $course))->toBeTrue();
});

it('is incomplete under an exam-based rule when no attempt exists yet', function (): void {
    $quiz = Quiz::factory()->exam()->create();
    $course = Course::factory()->create([
        'completion_rule' => new CourseCompletionRuleData(CourseCompletionRuleType::PassExam, examQuizId: $quiz->id),
    ]);
    $assignment = CourseAssignment::factory()->create();

    expect(completionChecker()->isComplete($assignment, $course))->toBeFalse();
});
