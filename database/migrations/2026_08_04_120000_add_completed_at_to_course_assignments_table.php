<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Purely a "have we already fired the one-time Completion
        // notification for this assignment" marker -- CourseCompletionChecker
        // stays the single source of truth for whether an assignment
        // actually *is* complete right now (it's computed fresh every time,
        // e.g. by IssueCertificate), this column never substitutes for that.
        Schema::table('course_assignments', function (Blueprint $table): void {
            $table->timestamp('completed_at')->nullable()->after('first_opened_at');
        });
    }

    public function down(): void
    {
        Schema::table('course_assignments', function (Blueprint $table): void {
            $table->dropColumn('completed_at');
        });
    }
};
