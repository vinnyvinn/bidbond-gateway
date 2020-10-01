<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;


class SendVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $code;

    public function __construct($user, $code)
    {
        $this->user = $user;
        $this->code = $code;
      //  info(env('EMAIL_VERIFICATION_URL').$code);
    }

    public function build()
    {
        return $this->subject(config('app.name').' Email Verification')
            ->markdown('emails.verification');
    }
}
