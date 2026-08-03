{{-- Included (not passed a data array) so it shares its calling notification view's own
     variables ($resellerName, $logoUrl) -- @slot content is compiled in the same PHP
     scope as the rest of that file, unlike a component's own props. --}}
<x-mail::header :url="config('app.url')">
@if (($logoUrl ?? null) !== null)
<img src="{{ $logoUrl }}" alt="{{ $resellerName ?? config('app.name') }}" class="logo">
@else
{{ $resellerName ?? config('app.name') }}
@endif
</x-mail::header>
