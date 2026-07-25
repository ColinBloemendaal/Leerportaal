<?php

declare(strict_types=1);

arch('controllers do not depend on models directly')
    ->expect('App\Http\Controllers')
    ->not->toUse('App\Models');

arch('actions and services do not use facades')
    ->expect(['App\Actions', 'App\Services'])
    ->not->toUse('Illuminate\Support\Facades');

arch('actions are final and readonly')
    ->expect('App\Actions')
    ->classes()
    ->toBeFinal()
    ->toBeReadonly();

arch('DTOs are final and readonly')
    ->expect('App\DataTransferObjects')
    ->classes()
    ->toBeFinal()
    ->toBeReadonly();

arch('enums are string-backed')
    ->expect('App\Enums')
    ->toBeStringBackedEnums();

arch('every FormRequest exposes toDto()')
    ->expect('App\Http\Requests')
    ->classes()
    ->toHaveMethod('toDto');

arch('only actions, repositories, and models touch models directly')
    ->expect('App\Models')
    ->toOnlyBeUsedIn([
        'App\Actions',
        'App\Contracts\Repositories',
        'App\Repositories',
        'App\Models',
        'App\Policies',
        'App\Http\Resources',
        'App\Tenancy',
        'App\Events',
        'Database\Factories',
        'Database\Seeders',
    ]);
