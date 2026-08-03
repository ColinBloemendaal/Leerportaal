@component('mail::message')
# Your notification digest

Here's what happened since your last digest:

@component('mail::table')
| |
| --- |
@foreach ($messages as $message)
| {{ $message }} |
@endforeach
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
