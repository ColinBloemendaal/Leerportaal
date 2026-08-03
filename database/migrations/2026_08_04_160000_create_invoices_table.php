<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reseller_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->date('period_start');
            $table->date('period_end');
            // Sum of its lines -- recomputed whenever a line is attached
            // while still a draft, then frozen the moment it's issued
            // (CLAUDE.md §11: "Invoices are immutable once issued").
            $table->unsignedInteger('total_cents')->default(0);
            $table->string('mollie_payment_id')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
