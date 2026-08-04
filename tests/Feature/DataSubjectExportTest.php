<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('shows the data export settings page to any authenticated user', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/settings/data-export')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Settings/DataExport'));
});

it('downloads the JSON export containing the user\'s own profile', function (): void {
    $user = User::factory()->create(['name' => 'Jane Cursist']);

    $response = $this->actingAs($user)->get('/settings/data-export/json');

    $response->assertOk()
        ->assertHeader('Content-Disposition', 'attachment; filename="my-data.json"')
        ->assertJsonPath('profile.name', 'Jane Cursist');
});

it('downloads the human-readable HTML export containing the user\'s own profile', function (): void {
    $user = User::factory()->create(['name' => 'Jane Cursist']);

    $response = $this->actingAs($user)->get('/settings/data-export/html');

    $response->assertOk()
        ->assertHeader('Content-Disposition', 'attachment; filename="my-data.html"')
        ->assertSee('Jane Cursist');
});

it('redirects guests to login', function (): void {
    $this->get('/settings/data-export')->assertRedirect('/login');
});
