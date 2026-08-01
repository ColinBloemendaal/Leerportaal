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
        Schema::create('reseller_mail_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id')->index();
            $table->foreign('reseller_id')->references('id')->on('resellers');
            $table->string('type');
            $table->string('subject');
            $table->text('body_markdown');
            $table->timestamps();
            $table->softDeletes();

            // A row only exists when a reseller has overridden that
            // notification type -- one override per (reseller, type).
            $table->unique(['reseller_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reseller_mail_templates');
    }
};
