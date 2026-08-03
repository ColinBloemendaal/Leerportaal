<?php

declare(strict_types=1);

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

it('shows the filtered attempts index to a reseller-side user', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $quiz = Quiz::factory()->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id, 'name' => 'Jane Cursist']);
    QuizAttempt::factory()->for($quiz)->for($cursist, 'user')->create();

    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    $this->actingAs($user)->get('/admin/reseller/attempts')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Reseller/Attempts/Index')
            ->where('attempts.data.0.cursist_name', 'Jane Cursist'));
});

it('denies platform staff from reaching the reseller attempts index', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/admin/reseller/attempts')->assertForbidden();
});
