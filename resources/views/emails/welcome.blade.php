@component('mail::message')
# Welcome, {{ $userName }}

Your account with **{{ $resellerName }}** is ready to go.

Thanks,<br>
{{ $resellerName }}
@endcomponent
