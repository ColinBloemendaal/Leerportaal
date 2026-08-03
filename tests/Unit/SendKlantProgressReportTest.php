<?php

declare(strict_types=1);

use App\Actions\Reporting\SendKlantProgressReport;
use App\Mail\KlantProgressReport;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    Mail::fake();
});

it('mails only klant-admins, not klant-managers or plain cursisten', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    $admin = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);
    $admin->assignRole('klant-admin');

    $manager = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);
    $manager->assignRole('klant-manager');

    User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);

    $sent = app(SendKlantProgressReport::class)($klant);

    expect($sent)->toBe(1);

    Mail::assertQueued(KlantProgressReport::class, function (KlantProgressReport $mail) use ($admin, $klant): bool {
        return $mail->klant->is($klant) && $mail->hasTo($admin->email);
    });

    Mail::assertQueued(KlantProgressReport::class, 1);
});

it('sends nothing and reports zero when a klant has no klant-admins', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);

    $sent = app(SendKlantProgressReport::class)($klant);

    expect($sent)->toBe(0);

    Mail::assertNothingSent();
});
