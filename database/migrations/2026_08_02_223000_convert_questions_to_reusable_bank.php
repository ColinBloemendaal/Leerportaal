<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Question bank per reseller, reusable across quizzes" is impossible
 * to express with questions.quiz_id as a required single-parent FK --
 * the same question needs to be attachable to more than one quiz. This
 * replaces that direct FK with the quiz_questions pivot (next
 * migration) and gives Question the same mixed-ownership shape as
 * Course/CourseCategory/Media (nullable reseller_id: null = a
 * platform-shared bank question, set = one reseller's own).
 *
 * questions.order is dropped too -- it was always "this question's
 * position within its one quiz", which no longer has a single answer
 * once a question can belong to several quizzes at once. Ordering now
 * lives on quiz_questions instead, per-attachment.
 *
 * Safe to drop-and-recreate rather than a reversible down(): both
 * columns were added this same session, no production data exists yet.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->dropColumn(['quiz_id', 'order']);
            $table->foreignId('reseller_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropColumn('reseller_id');
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
        });
    }
};
