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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            // Null = platform (catalog) course, visible to every
            // reseller. Set = owned by and visible only to that reseller.
            // Same shape as course_categories -- no TenantScoped, see
            // App\Models\Course.
            $table->unsignedBigInteger('reseller_id')->nullable()->index();
            $table->foreign('reseller_id')->references('id')->on('resellers');

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');

            // spatie/laravel-translatable JSON conversion for
            // title/description is a later task in this phase -- these
            // columns start as plain strings deliberately.
            $table->json('available_locales');

            // A repeat is a new course row, never a reassignment of an
            // existing one -- see CLAUDE.md §11.
            $table->unsignedSmallInteger('variant_year')->nullable();
            $table->foreignId('repeats_from_course_id')->nullable()->constrained('courses')->nullOnDelete();

            // Integer cents, cast through App\Casts\MoneyCast -- see
            // CLAUDE.md §4. reseller_set_price_cents only applies when
            // reseller_id is set; platform_price_cents only when it's
            // null. price_floor_override_cents can apply to either (a
            // platform admin's manual floor on a reseller-authored
            // course, CLAUDE.md §11) -- the billing calculation itself is
            // Phase 6, not this task.
            $table->unsignedInteger('reseller_set_price_cents')->nullable();
            $table->unsignedInteger('platform_price_cents')->nullable();
            $table->unsignedInteger('price_floor_override_cents')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
