<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Leerportaal') }}</title>

        @vite(['resources/sass/app.scss', 'resources/js/app.ts'])
        @inertiaHead
        @if (($themeCss ?? '') !== '')
            <style>{!! $themeCss !!}</style>
        @endif
    </head>
    <body>
        @inertia
    </body>
</html>
