<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyDirectorCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $creator;
    public $pass;

    public function __construct($user, $creator, $pass)
    {
        $this->user = $user;
        $this->creator = $creator;
        $this->pass = $pass;
    }

    public function build()
    {

        return $this->subject(config('app.name').' Director Account Created')
            ->markdown('emails.notify_director');
    }
}
