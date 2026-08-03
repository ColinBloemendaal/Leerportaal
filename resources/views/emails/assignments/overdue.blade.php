@component('mail::message')
@slot('header')
@include('emails.partials.branded-header')
@endslot

# Overdue

**{{ $courseTitle }}** was due on **{{ $deadlineAt }}** and hasn't been completed yet.

Thanks,<br>
{{ $resellerName ?? config('app.name') }}

@slot('footer')
@include('emails.partials.branded-footer')
@endslot
@endcomponent
