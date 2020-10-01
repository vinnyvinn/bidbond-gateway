@component('mail::message')<p>We have received your payment for {{ $company->name }} Bidbond booking for tender number {{ $bidbond->tender_no }}.</p>

@component('mail::table')|                              |                                            |
| ---------------------------- |:-------------------------------------------:|
| <b>Transaction Date:    </b> | {{ $payment->transaction_date }}            |
| <b>Transaction By:      </b> | {{ $payment->name }}                        |
| <b>Transaction Account: </b> | {{ $payment->account}}           |
| <b>Transaction Number: </b>  | {{ $payment->transaction_number }}          |
| <b>Transaction Method:</b>   | {{ $payment->payment_method }}              |
| <b>Amount             </b>   | {{ number_format($payment->amount) }}                |
@endcomponent

@component('mail::subcopy',[])
<p><b>Kind regards,</b></p>
<p><b>{{ config('app.name') }} Customer Experience Team.</b></p>
@endcomponent
@endcomponent
