@component('mail::message')
# Overdue

**{{ $courseTitle }}** was due on **{{ $deadlineAt }}** and hasn't been completed yet.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
