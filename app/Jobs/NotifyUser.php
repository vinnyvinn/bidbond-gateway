<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;
use App\Mail\NotifyUserCreated;

class NotifyUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $user;
    protected $creater;
    protected $pass;

    public function __construct($user, $creater, $pass)
    {

        $this->user = $user;
        $this->creater = $creater;
        $this->pass = $pass;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $email = new NotifyUserCreated($this->user, $this->creater, $this->pass);

        Mail::to($this->user->email)->queue($email);
    }
}
