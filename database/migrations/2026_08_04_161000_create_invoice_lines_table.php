<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * No reseller_id of its own -- ownership follows its parent Invoice
     * (same "owner-follows-parent" convention as Certificate following
     * CourseAssignment), so this table is never TenantScoped directly.
     */
    public function up(): void
    {
        Schema::create('invoice_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_assignment_id')->constrained()->cascadeOnDelete();
            // Snapshotted, not a live join to courses.title -- the course
            // could be renamed or deleted long after this line is issued,
            // and an issued invoice's own line text must never change
            // retroactively.
            $table->string('description');
            $table->unsignedInteger('amount_cents');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};
