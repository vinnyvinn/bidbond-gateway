<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanySearchReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $payment;


    public function __construct($company, $payment)
    {
        $this->company = $company;
        $this->payment = $payment;
    }

    public function build()
    {
        return $this->subject(config('app.name') . ' Company Search Receipt')
            ->markdown('emails.company_search_receipt');
    }
}
