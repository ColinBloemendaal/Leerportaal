<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Not every invoice line traces back to a CourseAssignment -- storage
     * overage charges are the first counter-example. Null here means "not
     * an assignment charge"; the line's own snapshotted description says
     * what it actually is instead of a discriminator column.
     */
    public function up(): void
    {
        Schema::table('invoice_lines', function (Blueprint $table): void {
            $table->foreignId('course_assignment_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_lines', function (Blueprint $table): void {
            $table->foreignId('course_assignment_id')->nullable(false)->change();
        });
    }
};
