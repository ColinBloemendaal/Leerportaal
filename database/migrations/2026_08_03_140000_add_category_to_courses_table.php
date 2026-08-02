<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Not added with the original courses table task -- deferred until a
 * real consumer needed it, per that task's own note. Phase 6's
 * category-level access grants (a reseller granted a whole category,
 * cascading to new courses added to it later) are that consumer.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('course_category_id')->nullable()->after('reseller_id')
                ->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_category_id');
        });
    }
};
