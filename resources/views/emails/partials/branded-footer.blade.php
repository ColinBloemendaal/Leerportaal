<x-mail::footer>
@if (($footerText ?? null) !== null)
{{ $footerText }}
@else
&copy; {{ date('Y') }} {{ $resellerName ?? config('app.name') }}. {{ __('All rights reserved.') }}
@endif
@if (($supportEmail ?? null) !== null || ($termsUrl ?? null) !== null || ($privacyUrl ?? null) !== null)

@if (($supportEmail ?? null) !== null)<a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>@endif
@if (($termsUrl ?? null) !== null) &middot; <a href="{{ $termsUrl }}">{{ __('Terms') }}</a>@endif
@if (($privacyUrl ?? null) !== null) &middot; <a href="{{ $privacyUrl }}">{{ __('Privacy') }}</a>@endif
@endif
</x-mail::footer>
