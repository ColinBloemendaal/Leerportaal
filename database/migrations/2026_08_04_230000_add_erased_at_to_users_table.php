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
        Schema::table('users', function (Blueprint $table) {
            // Nullable: null means "never erased". Stamped once, by
            // App\Actions\Gdpr\EraseDataSubject, both as proof of
            // compliance and to make a repeat erasure request a no-op.
            $table->timestamp('erased_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('erased_at');
        });
    }
};
