@component('mail::message')
<p>Hi {{ $user->firstname }},</p>
<h4>New Bid Bond</h4>

<p>Your Bid Bond application was successful. Kindly find attached the same.</p>

@component('mail::subcopy',[])
<p><b>Kind regards,</b></p>
<p><b>{{ config('app.name') }} Customer Experience Team.</b></p>
@endcomponent

@endcomponent
