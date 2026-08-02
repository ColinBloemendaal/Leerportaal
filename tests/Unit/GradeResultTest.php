<?php

declare(strict_types=1);

use App\DataTransferObjects\Questions\GradeResult;

it('is correct when fully awarded', function (): void {
    $result = GradeResult::correct(5.0);

    expect($result->pointsAwarded)->toBe(5.0)
        ->and($result->pointsPossible)->toBe(5.0)
        ->and($result->isCorrect())->toBeTrue();
});

it('is not correct when partially awarded', function (): void {
    $result = GradeResult::partial(2.0, 5.0);

    expect($result->pointsAwarded)->toBe(2.0)
        ->and($result->isCorrect())->toBeFalse();
});

it('awards zero points when incorrect', function (): void {
    $result = GradeResult::incorrect(5.0);

    expect($result->pointsAwarded)->toBe(0.0)
        ->and($result->isCorrect())->toBeFalse();
});

it('has no points awarded and no correctness verdict while pending manual grading', function (): void {
    $result = GradeResult::pendingManualGrading(10.0);

    expect($result->pointsAwarded)->toBeNull()
        ->and($result->requiresManualGrading)->toBeTrue()
        ->and($result->isCorrect())->toBeNull();
});

it('carries optional feedback', function (): void {
    $result = GradeResult::incorrect(5.0, 'Check your calculation.');

    expect($result->feedback)->toBe('Check your calculation.');
});
