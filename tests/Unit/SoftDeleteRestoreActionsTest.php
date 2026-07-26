<?php

declare(strict_types=1);

use App\Actions\Invites\RestoreInvite;
use App\Actions\ResellerKlanten\DeleteResellerKlant;
use App\Actions\ResellerKlanten\RestoreResellerKlant;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\UserInvite;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('soft deletes a klant', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    (new DeleteResellerKlant)($klant);

    expect(ResellerKlant::find($klant->id))->toBeNull()
        ->and(ResellerKlant::withTrashed()->find($klant->id))->not->toBeNull();
});

it('restores a soft-deleted klant', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();
    $klant->delete();

    (new RestoreResellerKlant)($klant);

    expect(ResellerKlant::find($klant->id))->not->toBeNull();
});

it('restores a revoked invite', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $invite = UserInvite::factory()->create(['reseller_id' => $reseller->id]);
    $invite->delete();

    (new RestoreInvite)($invite);

    expect(UserInvite::find($invite->id))->not->toBeNull();
});
