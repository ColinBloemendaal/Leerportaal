<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('notification_digest_frequency')->default('immediate')->after('platform_role');
            $table->timestamp('notification_digest_sent_at')->nullable()->after('notification_digest_frequency');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['notification_digest_frequency', 'notification_digest_sent_at']);
        });
    }
};
