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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();

            // No reseller_id here or on lessons/blocks: ownership lives
            // once on Course, everything under it inherits visibility
            // transitively through course_id -- duplicating reseller_id
            // down the hierarchy would just risk it drifting out of sync
            // with the course's own value.
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();

            $table->string('title');
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
        Schema::dropIfExists('modules');
    }
};
