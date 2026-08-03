@component('mail::message')
# Course completed

Nice work! You've completed **{{ $courseTitle }}**.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
