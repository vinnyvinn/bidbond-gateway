<?php

namespace App\Jobs;

use App\Mail\CompanySearchReceipt;
use App\Services\CompanyService;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class CompanySearchPaid implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $account;
    protected $user;
    protected $company;

    public function __construct($account, $user, $company)
    {
        $this->account = $account;
        $this->user = $user;
        $this->company = $company;
    }

    public function handle(PaymentService $paymentService)
    {
        $payment = json_decode($paymentService->checkPayment($this->account));
        Mail::to($this->user)
            ->cc([$this->company->email])
            ->queue(new CompanySearchReceipt($this->company, $payment));
    }
}
