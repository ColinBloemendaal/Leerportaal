<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_reminders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reseller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_assignment_id')->constrained()->cascadeOnDelete();
            // 'deadline' | 'overdue' -- App\Enums\NotificationType values,
            // not a separate enum, since these are exactly the two
            // catalogue entries this table exists to deduplicate sends for.
            $table->string('type');
            // Which configured reminder offset this row is for (days
            // before the deadline) -- null for 'overdue', which only ever
            // fires once, not once per offset.
            $table->unsignedSmallInteger('days_before')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['course_assignment_id', 'type', 'days_before']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_reminders');
    }
};
