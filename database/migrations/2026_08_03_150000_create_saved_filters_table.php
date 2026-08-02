<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A user's own named filter/sort presets for one of the admin indexes
 * (App\Enums\FilterableResource). Scoped by user_id only, not
 * TenantScoped -- a saved filter is a personal preference, not a
 * reseller-owned resource, and platform staff (no reseller_id) need to
 * be able to save presets too.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('saved_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('resource_type');
            $table->string('name');
            $table->json('filters');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_filters');
    }
};
