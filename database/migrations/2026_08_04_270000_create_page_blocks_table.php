<?php

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
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();

            // Plain string for now, same as blocks.type before the
            // "Block types" task firmed it up with an enum + registry --
            // App\Enums\PageBlockType is the next task's job.
            $table->string('type');
            $table->json('content')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('visible')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
    }
};
