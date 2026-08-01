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
        Schema::table('reseller_themes', function (Blueprint $table) {
            $table->string('sender_name')->nullable()->after('custom_css');
            $table->string('reply_to_email')->nullable()->after('sender_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reseller_themes', function (Blueprint $table) {
            $table->dropColumn(['sender_name', 'reply_to_email']);
        });
    }
};
