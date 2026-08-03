@component('mail::message')
# Certificate issued

Congratulations! You've completed **{{ $courseTitle }}** and your certificate is ready.

@component('mail::button', ['url' => $verificationUrl])
View your certificate
@endcomponent

Verification code: `{{ $verificationCode }}`

Thanks,<br>
{{ config('app.name') }}
@endcomponent
