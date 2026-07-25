<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

it('passes when every tenant table is consistently scoped', function (): void {
    $this->artisan('tenancy:audit')
        ->assertExitCode(0)
        ->expectsOutputToContain('Tenancy audit passed');
});

it('fails when a table has reseller_id but the model does not use TenantScoped', function (): void {
    Schema::table('resellers', function ($table): void {
        $table->unsignedBigInteger('reseller_id')->nullable();
    });

    $this->artisan('tenancy:audit')
        ->assertExitCode(1)
        ->expectsOutputToContain('App\Models\Reseller');
});
