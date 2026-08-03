@component('mail::message')
# New course assigned

You've been assigned **{{ $courseTitle }}**.

@if ($deadlineAt)
Please complete it by **{{ $deadlineAt }}**.
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
