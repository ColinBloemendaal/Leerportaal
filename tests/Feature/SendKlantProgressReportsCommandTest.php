<?php

declare(strict_types=1);

use App\Mail\KlantProgressReport;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Mail;

it('emails every klant-admin across every reseller, bypassing tenant scope', function (): void {
    Mail::fake();

    $resellerA = Reseller::factory()->create();
    app(TenantContext::class)->set($resellerA);
    $klantA = ResellerKlant::factory()->for($resellerA, 'reseller')->create();
    $adminA = User::factory()->create(['reseller_id' => $resellerA->id, 'resellerklant_id' => $klantA->id]);
    $adminA->assignRole('klant-admin');

    $resellerB = Reseller::factory()->create();
    app(TenantContext::class)->set($resellerB);
    $klantB = ResellerKlant::factory()->for($resellerB, 'reseller')->create();
    $adminB = User::factory()->create(['reseller_id' => $resellerB->id, 'resellerklant_id' => $klantB->id]);
    $adminB->assignRole('klant-admin');

    $this->artisan('reports:klant-progress')->assertSuccessful();

    Mail::assertQueued(KlantProgressReport::class, fn (KlantProgressReport $mail): bool => $mail->hasTo($adminA->email));
    Mail::assertQueued(KlantProgressReport::class, fn (KlantProgressReport $mail): bool => $mail->hasTo($adminB->email));
    Mail::assertQueued(KlantProgressReport::class, 2);
});
