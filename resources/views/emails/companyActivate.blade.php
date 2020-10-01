@component('mail::message')
<h4>Company Activation Email</h4>
<p>Dear {{ $user['firstname'] }},</p>
<p>Kindly click the button below to activate your Company account:</p>


@component('mail::button', ['url' => config('app.company_verification_url').$code, 'color' => 'primary' ])
Activate Account
@endcomponent

@component('mail::subcopy',[])
<p><b>Kind regards,</b></p>
<p><b>{{ config('app.name') }} Customer Experience Team.</b></p>
@endcomponent

@endcomponent
