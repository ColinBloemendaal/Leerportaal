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
        Schema::create('reseller_themes', function (Blueprint $table) {
            $table->id();
            // One theme per reseller.
            $table->foreignId('reseller_id')->unique()->constrained()->cascadeOnDelete();

            // Bootstrap's own default primary blue -- a reseller with no
            // customization yet still renders sensibly.
            $table->string('primary_color')->default('#0d6efd');
            $table->string('secondary_color')->nullable();
            $table->string('accent_color')->nullable();
            $table->string('font_family')->nullable();

            // Private disk, per CLAUDE.md §7 ("All uploads ... private disk
            // by default"). Store the disk path, not a public URL.
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('login_background_path')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reseller_themes');
    }
};
