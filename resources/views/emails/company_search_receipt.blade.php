@component('mail::message')
<p>We have received your payment for Company Search for {{ $company->name }} of registration number {{ $company->crp }}.</p>
@component('mail::table')
|                              |                                            |
| ---------------------------- |:-------------------------------------------:|
| <b>Transaction Date:    </b> | {{ $payment->transaction_date }}            |
| <b>Transaction By:      </b> | {{ $payment->name }}                        |
| <b>Transaction Account: </b> | {{ isset($payment->account) ? $payment->account : ''}}  |
| <b>Transaction Number: </b>  | {{ $payment->transaction_number }}          |
| <b>Transaction Method:</b>   | {{ $payment->payment_method }}              |
| <b>Amount             </b>   | {{ number_format($payment->amount) }}                |
@endcomponent

@component('mail::subcopy',[])
<p><b>Kind regards,</b></p>
<p><b>{{ config('app.name') }} Customer Experience Team.</b></p>
@endcomponent
@endcomponent
