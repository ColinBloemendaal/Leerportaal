<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widens courses.title and course_categories.name from string (255) to
 * text: spatie/laravel-translatable stores a {"nl": "...", "en": "..."}
 * map in the same column, which can exceed 255 chars across locales.
 * courses.description is already text, no change needed there.
 *
 * Drop-and-recreate rather than Blueprint::change() (which needs
 * doctrine/dbal, not installed) -- both tables are brand new with no
 * production data, so this is safe today.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('title');
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->text('title')->after('reseller_id');
        });

        Schema::table('course_categories', function (Blueprint $table) {
            $table->dropColumn('name');
        });
        Schema::table('course_categories', function (Blueprint $table) {
            $table->text('name')->after('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('title');
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->string('title')->after('reseller_id');
        });

        Schema::table('course_categories', function (Blueprint $table) {
            $table->dropColumn('name');
        });
        Schema::table('course_categories', function (Blueprint $table) {
            $table->string('name')->after('parent_id');
        });
    }
};
