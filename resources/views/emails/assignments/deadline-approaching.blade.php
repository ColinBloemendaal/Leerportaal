@component('mail::message')
@slot('header')
@include('emails.partials.branded-header')
@endslot

# Deadline approaching

**{{ $courseTitle }}** is due in {{ $daysBefore }} {{ $daysBefore === 1 ? 'day' : 'days' }}, on **{{ $deadlineAt }}**.

Thanks,<br>
{{ $resellerName ?? config('app.name') }}

@slot('footer')
@include('emails.partials.branded-footer')
@endslot
@endcomponent
