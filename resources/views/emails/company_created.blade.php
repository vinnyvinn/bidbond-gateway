@component('mail::message')
Your company {{ $company['name'] }} has been successfully registered on {{ config('app.name') }}.

The company details are:

<b>Registration Number:</b> {{ $company['crp'] }}<br>
<b>Email:</b> {{ $company['email'] }}<br>
<b>Phone:</b> {{ $company['phone_number'] }}<br>
<b>Physical Address:</b> {{ $company['physical_address'] }}<br>
<b>Postal Address:</b> {{ $company['postal_address'] }}<br>
<b>Postal Code:</b> {{ isset($company['postal_code']->code) ? $company['postal_code']->code.' '.$company['postal_code']->name :''}}

Login using the details below:

<b>Email: </b>{{ $company['email'] }}
@if($password)
<b>Password: </b>{{ $password }}
@endif

@component('mail::button', ['url' => config('app.web_url'), 'color' => 'primary' ])
Login
@endcomponent

@component('mail::subcopy',[])
<p><b>Kind regards,</b></p>
<p><b>{{ config('app.name') }} Customer Experience Team.</b></p>
@endcomponent
@endcomponent
