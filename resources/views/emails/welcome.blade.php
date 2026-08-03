@component('mail::message')
@slot('header')
@include('emails.partials.branded-header')
@endslot

# Welcome, {{ $userName }}

Your account with **{{ $resellerName }}** is ready to go.

Thanks,<br>
{{ $resellerName }}

@slot('footer')
@include('emails.partials.branded-footer')
@endslot
@endcomponent
