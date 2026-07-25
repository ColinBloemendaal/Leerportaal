<?php

declare(strict_types=1);

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
        // Auth controllers need to know "the current actor is a User" to
        // pass identity into Actions -- a narrower, different concern than
        // the business-logic-querying this rule otherwise guards against
        // (e.g. a controller doing ResellerKlant::where(...)->get()
        // instead of going through a Repository). No other controllers are
        // exempt.
        'App\Http\Controllers\Auth',
        'Database\Factories',
        'Database\Seeders',
    ]);
