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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            // Exactly one of these two is set -- a quiz is either an
            // in-lesson knowledge check or an end-of-module exam. Enforced
            // in App\Models\Quiz (a saving guard), not a DB CHECK
            // constraint, since CLAUDE.md §8 requires SQLite-in-memory to
            // work for tests and CHECK constraint syntax/support isn't
            // portable across SQLite/MySQL in the same way FKs are.
            $table->foreignId('module_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('type');
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
