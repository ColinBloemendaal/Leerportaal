<?php

declare(strict_types=1);

use App\Console\Commands\TenancyAuditCommand;

it('flags a table with reseller_id whose model does not use TenantScoped', function (): void {
    $problem = TenancyAuditCommand::describeInconsistency('App\Models\Example', 'examples', hasColumn: true, usesTrait: false);

    expect($problem)
        ->toContain('App\Models\Example')
        ->toContain('does not use TenantScoped');
});

it('flags a TenantScoped model whose table has no reseller_id', function (): void {
    $problem = TenancyAuditCommand::describeInconsistency('App\Models\Example', 'examples', hasColumn: false, usesTrait: true);

    expect($problem)
        ->toContain('App\Models\Example')
        ->toContain('has no reseller_id column');
});

it('is silent when both are true', function (): void {
    expect(TenancyAuditCommand::describeInconsistency('App\Models\Example', 'examples', hasColumn: true, usesTrait: true))
        ->toBeNull();
});

it('is silent when both are false', function (): void {
    expect(TenancyAuditCommand::describeInconsistency('App\Models\Example', 'examples', hasColumn: false, usesTrait: false))
        ->toBeNull();
});
