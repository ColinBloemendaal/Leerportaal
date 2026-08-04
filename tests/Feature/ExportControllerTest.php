<?php

declare(strict_types=1);

use App\Enums\ExportStatus;
use App\Models\Export;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia;

it('requests an export for platform staff and dispatches the job', function (): void {
    Queue::fake();
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)
        ->post('/admin/exports', ['resource_type' => 'resellers'])
        ->assertRedirect();

    $this->assertDatabaseHas('exports', [
        'user_id' => $staff->id,
        'resource_type' => 'resellers',
        'status' => ExportStatus::Pending->value,
    ]);
});

it('denies a reseller-side user from exporting a platform-only resource', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/exports', ['resource_type' => 'resellers'])->assertForbidden();
});

it('requests an export of invoices for a reseller-side user', function (): void {
    Queue::fake();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/exports', ['resource_type' => 'invoices'])
        ->assertRedirect();

    $this->assertDatabaseHas('exports', [
        'user_id' => $user->id,
        'resource_type' => 'invoices',
        'status' => ExportStatus::Pending->value,
    ]);
});

it('denies platform staff from exporting the reseller-scoped invoices resource', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->post('/admin/exports', ['resource_type' => 'invoices'])->assertForbidden();
});

it('lists a user\'s own exports with a signed download link when completed', function (): void {
    $user = User::factory()->create();
    $ownExport = Export::factory()->completed()->create(['user_id' => $user->id]);
    Export::factory()->completed()->create(); // someone else's

    $this->actingAs($user)->get('/admin/exports')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Exports/Index')
            ->where('exports.data.0.id', $ownExport->id)
            ->has('exports.data', 1));
});

it('downloads a completed export via its signed URL', function (): void {
    Storage::fake('local');
    $user = User::factory()->create();
    $export = Export::factory()->completed()->create(['user_id' => $user->id]);
    Storage::disk('local')->put($export->path, "id,name\n1,Acme\n");

    $url = URL::temporarySignedRoute('admin.exports.download', $export->expires_at, ['export' => $export->id]);

    $this->actingAs($user)->get($url)->assertOk();
});

it('denies downloading someone else\'s export even with the right signature shape', function (): void {
    Storage::fake('local');
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $export = Export::factory()->completed()->create(['user_id' => $owner->id]);
    Storage::disk('local')->put($export->path, "id,name\n1,Acme\n");

    $url = URL::temporarySignedRoute('admin.exports.download', $export->expires_at, ['export' => $export->id]);

    $this->actingAs($stranger)->get($url)->assertNotFound();
});

it('rejects an unsigned or tampered download link', function (): void {
    $user = User::factory()->create();
    $export = Export::factory()->completed()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get("/admin/exports/{$export->id}/download")->assertForbidden();
});

it('rejects an expired export even with a validly-signed link generated before expiry', function (): void {
    Storage::fake('local');
    $user = User::factory()->create();
    $export = Export::factory()->create([
        'user_id' => $user->id,
        'status' => ExportStatus::Completed,
        'disk' => 'local',
        'path' => 'exports/expired.csv',
        'expires_at' => now()->addMinute(),
    ]);
    Storage::disk('local')->put($export->path, "id,name\n1,Acme\n");

    $url = URL::temporarySignedRoute('admin.exports.download', $export->expires_at, ['export' => $export->id]);

    $this->travel(2)->minutes();

    $this->actingAs($user)->get($url)->assertForbidden();
});
