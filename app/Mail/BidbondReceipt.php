<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BidbondReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public $bidbond;
    public $company;
    public $payment;


    public function __construct($bidbond, $company, $payment)
    {
        $this->bidbond = $bidbond;
        $this->company = $company;
        $this->payment = $payment;
    }

    public function build()
    {
        return $this->subject(config('app.name') . ' Bidbond  Receipt')
            ->markdown('emails.bidbond_receipt');
    }
}
