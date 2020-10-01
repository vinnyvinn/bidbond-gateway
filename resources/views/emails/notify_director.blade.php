@component('mail::message')
<p>Dear {{ $user->firstname }} {{ $user->lastname }},</p>
<p>You have been registered on {{ config('app.name') }} as  <b>{{ $user->getRoles()[0] }}</b> by {{$creator}}.
Click the link sent through sms to your phone to activate your account.
</p>

@component('mail::subcopy',[])
<p>If this email doesn't make any sense to you please ignore.</p><br>
<p><b>Kind regards,</b></p>
<p><b>{{ config('app.name') }} Customer Experience Team.</b></p>
@endcomponent
@endcomponent
