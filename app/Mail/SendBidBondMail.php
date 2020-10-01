<?php

namespace App\Mail;

use function base64_decode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBidBondMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pdf;
    public $bid;
    public $user;


    public function __construct($user, $pdf, $bid)
    {
        $this->pdf = $pdf;
        $this->bid = $bid;
        $this->user = $user;

    }

    public function build()
    {
        return $this->subject('Bid Bond Issued  - ' . $this->bid->secret)
            ->markdown('emails.bidbond')->attachData(base64_decode($this->pdf), $this->bid->company_id . '_Bid Bond.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
