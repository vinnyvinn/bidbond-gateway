@component('mail::message')
<p>Dear {{ $user->firstname }} {{ $user->lastname }},</p>
<p>You have been registered on {{config('app.name')}} as  <b>{{ $user->getRoles()[0] }}</b> by {{$creator}}.</p>
<p>Username : {{$user->email}}<br> Password : {{$pass}}</p>
<p>Click the below button to activate your account.</p>
@component('mail::button', ['url' => config('app.web_url'), 'color' => 'primary' ])
    Login to Account
@endcomponent
<p>If this email doesn't make any sense to you please ignore.</p><br>
<p><b>Kind regards,</b></p>
<p><b>{{config('app.name')}} Customer Experience Team.</b></p>
@endcomponent
