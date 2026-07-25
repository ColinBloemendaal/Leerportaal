<?php

use App\Http\Controllers\Auth\TenantLoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/login/{slug}', TenantLoginController::class)->name('tenant.login');
