<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CLAUDE.md §11: "Authoring add-on: €250/year unlocks custom course
     * creation." Null = never purchased; a future timestamp = active; a
     * past timestamp = lapsed -- one column expresses all three states,
     * no separate boolean needed.
     */
    public function up(): void
    {
        Schema::table('resellers', function (Blueprint $table): void {
            $table->timestamp('authoring_addon_expires_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('resellers', function (Blueprint $table): void {
            $table->dropColumn('authoring_addon_expires_at');
        });
    }
};
