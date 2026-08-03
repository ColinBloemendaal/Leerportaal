@component('mail::message')
@slot('header')
@include('emails.partials.branded-header')
@endslot

# New course assigned

You've been assigned **{{ $courseTitle }}**.

@if ($deadlineAt)
Please complete it by **{{ $deadlineAt }}**.
@endif

Thanks,<br>
{{ $resellerName ?? config('app.name') }}

@slot('footer')
@include('emails.partials.branded-footer')
@endslot
@endcomponent
