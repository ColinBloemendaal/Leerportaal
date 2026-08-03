<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * total_cents used to mean "sum of lines" -- now that VAT exists, that
     * meaning moves to subtotal_cents, and total_cents becomes the actual
     * grand total charged (subtotal + vat_cents, or exactly subtotal on a
     * reverse-charge invoice). Both are computed once, at issue time (see
     * App\Actions\Billing\IssueInvoice), never while still a draft --
     * VAT/reverse-charge treatment depends on the reseller's country and
     * VAT ID *at that moment*, not at line-accumulation time.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->renameColumn('total_cents', 'subtotal_cents');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->unsignedTinyInteger('vat_rate_percent')->default(0)->after('subtotal_cents');
            $table->unsignedInteger('vat_cents')->default(0)->after('vat_rate_percent');
            $table->boolean('reverse_charge')->default(false)->after('vat_cents');
            $table->unsignedInteger('total_cents')->default(0)->after('reverse_charge');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn(['vat_rate_percent', 'vat_cents', 'reverse_charge', 'total_cents']);
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->renameColumn('subtotal_cents', 'total_cents');
        });
    }
};
