<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Platform-wide, no reseller_id: a hard bounce or spam complaint is a
     * property of the recipient mailbox, not of whichever reseller's course
     * happened to email it -- an address that's undeliverable stays
     * undeliverable no matter which tenant tries to reach it next.
     */
    public function up(): void
    {
        Schema::create('suppressed_emails', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->string('reason');
            // The provider's own event name (e.g. "permanent_fail",
            // "complained") -- kept verbatim alongside our normalized
            // `reason` enum for support/debugging, never parsed back.
            $table->string('provider_event_type');
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppressed_emails');
    }
};
