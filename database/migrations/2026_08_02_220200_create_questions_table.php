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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();

            // Plain string for now, not yet enum-cast -- QuestionTypeEnum
            // is the next task's job (CLAUDE.md §5's own split: the type
            // names/states first, the per-type editor/player/grading
            // classes after), same as blocks.type was before BlockTypeEnum
            // existed.
            $table->string('type');
            // Translatable per CLAUDE.md §5 ("prompt (json/translatable)"),
            // same JSON-per-locale-as-text-column shape as courses.title.
            $table->text('prompt')->nullable();
            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->json('settings')->nullable();
            $table->json('payload')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
