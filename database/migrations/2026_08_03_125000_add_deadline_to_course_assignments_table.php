<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * reminder_days_before is deliberately just configuration (which offsets
 * before the deadline should trigger a reminder) -- the actual mailable/
 * notification dispatch and its own sent-tracking are Phase 8's job
 * (Notifications, billing, GDPR), not enrollment data modeling.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('course_assignments', function (Blueprint $table) {
            $table->timestamp('deadline_at')->nullable()->after('assigned_at');
            $table->json('reminder_days_before')->nullable()->after('deadline_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_assignments', function (Blueprint $table) {
            $table->dropColumn(['deadline_at', 'reminder_days_before']);
        });
    }
};
