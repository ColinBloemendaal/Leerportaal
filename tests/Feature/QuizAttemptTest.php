<?php

declare(strict_types=1);

use App\DataTransferObjects\Courses\CourseCompletionRuleData;
use App\DataTransferObjects\Quizzes\QuizSettingsData;
use App\Enums\CourseCompletionRuleType;
use App\Enums\QuestionTypeEnum;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Module;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Services\Progress\CourseCompletionChecker;
use Inertia\Testing\AssertableInertia;

it('shows the quiz-taking page with the frozen question set', function (): void {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $quiz = Quiz::factory()->exam()->forModule($module->id)->create();
    $question = Question::factory()->create(['type' => QuestionTypeEnum::TrueFalse]);
    $quiz->questions()->attach($question->id, ['order' => 0]);
    $user = User::factory()->create();
    CourseAssignment::factory()->for($course)->for($user, 'user')->create();

    $this->actingAs($user)->get("/quizzes/{$quiz->id}/attempt")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Quizzes/Attempt')
            ->where('blockedReason', null)
            ->has('attempt.questions', 1)
            ->where('attempt.questions.0.questionId', $question->id));
});

it('denies a user with no assignment for the quiz\'s course', function (): void {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $quiz = Quiz::factory()->exam()->forModule($module->id)->create();
    $user = User::factory()->create();

    $this->actingAs($user)->get("/quizzes/{$quiz->id}/attempt")->assertForbidden();
});

it('submits an attempt, grades it, and completes the course under a pass_exam rule', function (): void {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $quiz = Quiz::factory()->exam()->forModule($module->id)->withSettings(new QuizSettingsData(passThresholdPercent: 50))->create();
    $course->update(['completion_rule' => new CourseCompletionRuleData(CourseCompletionRuleType::PassExam, examQuizId: $quiz->id)]);
    $question = Question::factory()->create(['type' => QuestionTypeEnum::TrueFalse, 'points' => 10, 'payload' => ['correct_answer' => true]]);
    $quiz->questions()->attach($question->id, ['order' => 0]);
    $user = User::factory()->create();
    $assignment = CourseAssignment::factory()->for($course)->for($user, 'user')->create();

    $this->actingAs($user)->get("/quizzes/{$quiz->id}/attempt");
    $attempt = $user->quizAttempts()->where('quiz_id', $quiz->id)->first();

    $this->actingAs($user)
        ->post("/quiz-attempts/{$attempt->id}/submit", ['answers' => [$question->id => true]])
        ->assertRedirect();

    expect(app(CourseCompletionChecker::class)->isComplete($assignment->fresh(), $course->fresh()))->toBeTrue();
});

it('denies a stranger from submitting someone else\'s attempt', function (): void {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $quiz = Quiz::factory()->exam()->forModule($module->id)->create();
    $question = Question::factory()->create(['type' => QuestionTypeEnum::TrueFalse]);
    $quiz->questions()->attach($question->id, ['order' => 0]);
    $owner = User::factory()->create();
    CourseAssignment::factory()->for($course)->for($owner, 'user')->create();
    $stranger = User::factory()->create();

    $this->actingAs($owner)->get("/quizzes/{$quiz->id}/attempt");
    $attempt = $owner->quizAttempts()->where('quiz_id', $quiz->id)->first();

    $this->actingAs($stranger)
        ->post("/quiz-attempts/{$attempt->id}/submit", ['answers' => [$question->id => true]])
        ->assertForbidden();
});
