@component('mail::message')
# Deadline approaching

**{{ $courseTitle }}** is due in {{ $daysBefore }} {{ $daysBefore === 1 ? 'day' : 'days' }}, on **{{ $deadlineAt }}**.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
