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
        // Mailables shape their own view data from a model, the same job
        // Http\Resources does for Inertia props.
        'App\Mail',
        // Notification classes are typed over Illuminate\Notifications\Notification's
        // own via($notifiable)/toMail($notifiable)/toDatabase($notifiable)
        // signatures -- same "shapes its own payload from an already-loaded
        // model" reasoning as App\Mail above.
        'App\Notifications',
        // Services do pure calculation over domain models (CLAUDE.md §3a's
        // own examples -- GradingService, AssignmentPricingService --
        // fundamentally need to accept a Question/CourseAssignment to
        // calculate over). Reads still belong in Repositories; this is
        // about accepting an already-loaded model as an argument, not
        // querying one directly.
        'App\Services',
        // QuestionType::grade() takes the Question model directly per
        // CLAUDE.md §5's own literal signature -- same reasoning as
        // App\Services above, just declared on an interface/its
        // implementations instead of a service class.
        'App\Questions',
        'Database\Factories',
        'Database\Seeders',
    ]);
