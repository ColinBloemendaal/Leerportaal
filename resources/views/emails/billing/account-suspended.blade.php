@component('mail::message')
@slot('header')
@include('emails.partials.branded-header')
@endslot

# Account suspended

Your account with **{{ $resellerName }}** has been suspended because an invoice remains unpaid after several payment attempts.

Please settle the outstanding invoice to restore access.

Thanks,<br>
{{ $resellerName }}

@slot('footer')
@include('emails.partials.branded-footer')
@endslot
@endcomponent
