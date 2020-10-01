<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $email;
    public $password;

    public function __construct($company,$email,$password)
    {
        $this->company = $company;
        $this->email = $email;
        $this->password = $password;
    }

    public function build()
    {
       return $this->subject(config('app.name') . ' Company Onboarded')->markdown('emails.company_created');
    }
}
