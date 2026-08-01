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
            $table->text('footer_text')->nullable()->after('reply_to_email');
            $table->string('support_email')->nullable()->after('footer_text');
            $table->string('terms_url')->nullable()->after('support_email');
            $table->string('privacy_url')->nullable()->after('terms_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reseller_themes', function (Blueprint $table) {
            $table->dropColumn(['footer_text', 'support_email', 'terms_url', 'privacy_url']);
        });
    }
};
