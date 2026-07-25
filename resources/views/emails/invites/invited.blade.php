@component('mail::message')
# You've been invited

{{ $resellerName }} has invited **{{ $inviteeName }}** to join their Leerportaal environment.

@component('mail::button', ['url' => $acceptUrl])
Accept invitation
@endcomponent

This link expires in 7 days and can only be used once.

If you weren't expecting this invitation, you can safely ignore this email.

Thanks,<br>
{{ $resellerName }}
@endcomponent
