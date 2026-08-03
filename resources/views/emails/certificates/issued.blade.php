@component('mail::message')
@slot('header')
@include('emails.partials.branded-header')
@endslot

# Certificate issued

Congratulations! You've completed **{{ $courseTitle }}** and your certificate is ready.

@component('mail::button', ['url' => $verificationUrl])
View your certificate
@endcomponent

Verification code: `{{ $verificationCode }}`

Thanks,<br>
{{ $resellerName ?? config('app.name') }}

@slot('footer')
@include('emails.partials.branded-footer')
@endslot
@endcomponent
