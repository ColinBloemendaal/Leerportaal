@component('mail::message')
@slot('header')
@include('emails.partials.branded-header')
@endslot

# Course completed

Nice work! You've completed **{{ $courseTitle }}**.

Thanks,<br>
{{ $resellerName ?? config('app.name') }}

@slot('footer')
@include('emails.partials.branded-footer')
@endslot
@endcomponent
