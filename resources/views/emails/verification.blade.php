@component('mail::message')
<h4>{{ config('app.name') }} Activation Email</h4>
<p>Dear User,</p>
<p>You have been registered on {{ config('app.name') }}.</p>
<p>Click the below button to activate your account</p>


@component('mail::button', ['url' => config('app.email_verification_url').$code, 'color' => 'green' ])
Activate
@endcomponent

@component('mail::subcopy',[])
<p><b>Kind regards,</b></p>
<p><b>{{ config('app.name') }} Customer Experience Team.</b></p>
@endcomponent

@endcomponent

