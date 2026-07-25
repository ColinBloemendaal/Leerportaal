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
        Schema::table('users', function (Blueprint $table) {
            // Nullable: null means platform staff. See CLAUDE.md §1/§2.
            $table->foreignId('reseller_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('resellerklant_id')->nullable()->after('reseller_id')
                ->constrained('resellerklanten')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resellerklant_id');
            $table->dropConstrainedForeignId('reseller_id');
        });
    }
};
