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
        Schema::table('resellers', function (Blueprint $table) {
            $table->timestamp('dpa_accepted_at')->nullable();
            // Compared against config('gdpr.dpa_version') -- a mismatch
            // (including null, for a reseller who never accepted) means
            // this reseller needs to review and accept the current DPA.
            $table->string('dpa_accepted_version')->nullable();
            $table->foreignId('dpa_accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resellers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dpa_accepted_by_user_id');
            $table->dropColumn(['dpa_accepted_at', 'dpa_accepted_version']);
        });
    }
};
