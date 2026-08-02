<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();

            // Null = platform category, visible to every reseller. Set =
            // owned by that reseller only. No TenantScoped here on
            // purpose -- see App\Contracts\Repositories\CourseCategoryRepository.
            $table->unsignedBigInteger('reseller_id')->nullable()->index();
            $table->foreign('reseller_id')->references('id')->on('resellers');

            $table->foreignId('parent_id')->nullable()->constrained('course_categories')->nullOnDelete();

            $table->string('name');
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_categories');
    }
};
