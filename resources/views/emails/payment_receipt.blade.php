@component('mail::message')
<p>Dear {{ $company->name }},</p>
<br>
<p>Your company search cost breakdown is as below:</p><br>

<p><b>Date: </b> {{date('Y-m-d')}}</p>
<p><b>Phone Number: </b>{{$details->phone}}</p>
<p><b>Transaction Number: </b>{{ $details->transaction_number }}</p>
<hr>
<p><b>Amount: </b>{{ $details->amount }}</p>
@component('mail::table')|                              |                                            |
| ---------------------------- |:------------------------------------------:|
| <b>Total Requested Amount (kes)</b> | {{ number_format($bond_amount) }}   |
| <b>Valid For</b>                    | {{ $tenure }}                       |
| <b>Procuring Entity</b>             | {{$counterparty}}                   |
| <b>Bid Bond Cost (kes)</b>          | {{ number_format($bidcost) }}       |
| <b>Excise Duty (kes)</b>            | {{ number_format($dutyamount) }}    |
| <b>Total Cost (kes):</b>            | <b>{{ number_format($charge) }}</b> |
@endcomponent

<p><b>Kind regards,</b></p> <br>

<p><b>{{ config('app.name') }} Customer Experience Team.</b></p>

@endcomponent
