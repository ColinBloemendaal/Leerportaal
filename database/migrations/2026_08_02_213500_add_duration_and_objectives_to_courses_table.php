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
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('estimated_duration_minutes')->nullable()->after('description');
            // Deliberately NOT translatable: it's a plain list<string>, not
            // per-locale content -- adding it to Course::$translatable would
            // also pull it into TranslationCompletenessService's publish
            // gate (that service checks every translatable field), silently
            // making publish stricter for a task that only asked for the
            // field to exist. If localized objectives become a real need
            // later, that's a small follow-up, not a reason to widen the
            // gate speculatively now.
            $table->json('learning_objectives')->nullable()->after('estimated_duration_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['estimated_duration_minutes', 'learning_objectives']);
        });
    }
};
