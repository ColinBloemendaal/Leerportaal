<?php

use App\Http\Controllers\Auth\TenantLoginController;
use App\Http\Controllers\ResellerKlantController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/login/{slug}', TenantLoginController::class)->name('tenant.login');

// Reference vertical slice (FormRequest -> DTO -> Action -> Repository ->
// Inertia Resource), copyable for later features -- see CLAUDE.md §0.
// No login flow exists yet (Phase 1 task 29), so these aren't gated behind
// `auth` yet; that middleware is added once login is real.
Route::get('/klanten', [ResellerKlantController::class, 'index'])
    ->middleware('can:viewAny,App\Models\ResellerKlant')
    ->name('klanten.index');

Route::post('/klanten', [ResellerKlantController::class, 'store'])
    ->name('klanten.store');
