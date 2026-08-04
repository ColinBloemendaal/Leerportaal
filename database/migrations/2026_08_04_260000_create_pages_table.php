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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->constrained()->cascadeOnDelete();

            // App\Enums\PageTemplate: a fixed set (home/course_overview/
            // login/about/contact), not an open-ended slug -- CLAUDE.md
            // §1's "Level 2 (block templates)" is a closed set of
            // template slots per reseller, never arbitrary custom pages.
            $table->string('template');
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['reseller_id', 'template']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
