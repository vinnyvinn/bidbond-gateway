<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendBidBondMail;

class SendBidBond implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $pdf;
    protected $bid;
    public $user;

    public function __construct($user, $pdf, $bid)
    {
        $this->user = $user;
        $this->pdf = $pdf;
        $this->bid = $bid;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $email = new SendBidBondMail($this->user, $this->pdf, $this->bid);

        Mail::to($this->user->email)->queue($email);
    }
}
