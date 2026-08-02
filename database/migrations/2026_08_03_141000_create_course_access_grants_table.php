<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform -> reseller: a platform admin grants a reseller access to
 * either one catalog course or a whole category (never both -- enforced
 * the same way Quiz enforces "exactly one of module_id/lesson_id").
 * Category grants are read live (never snapshotted), so a new course
 * added to an already-granted category is automatically included --
 * see CourseAccessChecker.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_access_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('course_category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('granted_at');
            // Revoking sets this rather than deleting the row -- keeps
            // the audit trail and, per this phase's own requirement,
            // never cascades into deleting progress/assignments/
            // certificates, which don't even reference this table.
            $table->timestamp('revoked_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_access_grants');
    }
};
