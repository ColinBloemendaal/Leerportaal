<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reseller -> resellerklant: which of the reseller's own accessible
 * courses a specific resellerklant is allowed to assign to its
 * cursisten. Course-only, no category level -- unlike the platform ->
 * reseller grant, this task's wording never mentions category grants at
 * this level. Layered on top of CourseAccessChecker: a resellerklant
 * can only be granted a course the reseller itself already has access
 * to (own course, direct platform grant, or category platform grant).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resellerklant_course_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resellerklant_id')->constrained('resellerklanten')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('granted_at');
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
        Schema::dropIfExists('resellerklant_course_grants');
    }
};
