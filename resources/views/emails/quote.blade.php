@component('mail::message')
<h4>{{ config('app.name') }} Quote</h4>
<p>Dear @if($user){{ $user->firstname }} {{ $user->lastname }}@else Customer @endif,</p>
<p>Thank you for your interest in our products. Please find the quotation below:</p>
@component('mail::table')
|                              |                                            |
| ---------------------------- |:------------------------------------------:|
| <b>Total Requested Amount (kes)</b> | {{ number_format($bond_amount) }}   |
| <b>Valid For</b>                    | {{ $tenure }} days                  |
| <b>Procuring Entity</b>             | {{$counterparty}}                   |
| <b>Bid Bond Cost (kes)</b>          | {{ number_format($bidcost) }}       |
| <b>Excise Duty (kes)</b>            | {{ number_format($dutyamount) }}    |
| <b>Idemnity Cost (kes)</b>          | {{ number_format($idemnity_cost) }}  |
| <b>Total Cost (kes):</b>            | <b>{{ number_format($charge) }}</b> |
@endcomponent

@component('mail::button', ['url' => config('app.quote_get_url'), 'color' => 'primary' ])
Get Bid Bond
@endcomponent

@component('mail::subcopy',[])
<p><b>Kind regards,</b></p>
<p><b>{{ config('app.name') }} Customer Experience Team.</b></p>
@endcomponent

@endcomponent
