<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();

            // Position this question appeared in for this specific
            // attempt -- can differ per attempt when the quiz shuffles
            // (QuizQuestionRandomizer), so it can't live on the question
            // or the quiz_questions pivot.
            $table->unsignedInteger('order');

            // The submitted answer, in whatever shape that question
            // type's grade() expects. Set once at submission and never
            // mutated afterward -- only the grading fields below change
            // on a regrade, so the original answer is always retained.
            $table->json('answer')->nullable();

            // points_possible is snapshotted at answer time (not read
            // live from questions.points), since a bank question's point
            // value can change after this attempt was graded -- the
            // historical record should reflect what was actually
            // possible at the time, same reasoning Money/points get
            // snapshotted anywhere else historical accuracy matters.
            $table->float('points_awarded')->nullable();
            $table->float('points_possible');
            $table->boolean('is_correct')->nullable();
            $table->boolean('requires_manual_grading')->default(false);
            $table->text('feedback')->nullable();
            $table->timestamp('graded_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['quiz_attempt_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_answers');
    }
};
