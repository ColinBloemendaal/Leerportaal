<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\User;

it('lets platform staff erase a reseller-side user\'s personal data', function (): void {
    $target = User::factory()->create(['name' => 'Jane Cursist', 'email' => 'jane@example.test']);
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)
        ->post("/admin/platform/users/{$target->id}/erase")
        ->assertRedirect('/admin/platform/users');

    $target->refresh();

    expect($target->name)->toBe('Erased user')
        ->and($target->erased_at)->not->toBeNull()
        ->and($target->trashed())->toBeTrue();
});

it('lets a reseller admin erase a user within their own reseller', function (): void {
    $reseller = Reseller::factory()->create();
    $admin = User::factory()->create(['reseller_id' => $reseller->id]);
    $target = User::factory()->create(['reseller_id' => $reseller->id]);

    $this->actingAs($admin)->post("/admin/platform/users/{$target->id}/erase")->assertRedirect();

    expect($target->fresh()->erased_at)->not->toBeNull();
});

it('denies erasing a user in a different reseller', function (): void {
    $admin = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($admin)->post("/admin/platform/users/{$target->id}/erase")->assertForbidden();

    expect($target->fresh()->erased_at)->toBeNull();
});

it('denies erasing yourself', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->post("/admin/platform/users/{$staff->id}/erase")->assertForbidden();
});

it('404s for a non-existent user', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->post('/admin/platform/users/999999/erase')->assertNotFound();
});
