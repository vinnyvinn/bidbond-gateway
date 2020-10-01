@component('mail::message')
<h4>Password Reset</h4>
<p>Hi {{ $user->firstname }},</p>
<p>Kindly click the button below to reset password your {{ config('app.name') }} account</p>


@component('mail::button', ['url' => config('app.password_reset_url').$code, 'color' => 'green' ])
Reset Password
@endcomponent

@component('mail::subcopy',[])
<p><b>Kind regards,</b></p>
<p><b>{{ config('app.name') }} Customer Experience Team.</b></p>
@endcomponent
@endcomponent

