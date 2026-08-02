<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deliberately minimal "versioning": an incrementing counter and a
 * timestamp, not full content snapshots. Nothing consumes historical
 * versions yet (no enrollment/assignment exists to pin a cursist to a
 * specific version) -- that's Phase 5's job once it's a real need, not
 * a hypothetical one to build ahead of.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(0)->after('status');
            $table->timestamp('published_at')->nullable()->after('version');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['version', 'published_at']);
        });
    }
};
