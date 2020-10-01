<?php

namespace App\Jobs;

use App\Traits\InvokeSms;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Knox\AFT\AFT;
use Illuminate\Support\Facades\Log;
use Knox\AFT\Exceptions\ATFException;


class SendSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phone_number;
    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($phone_number, $message)
    {
        $this->phone_number = $phone_number;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws ATFException
     */
    public function handle()
    {
        if (config('aft.enable_sms')) {
            //AFT::sendMessage($this->phone_number, $this->message);
            InvokeSms::initSms($this->phone_number,$this->message);
        }
        Log::info($this->phone_number . " : " . $this->message);

    }
}
