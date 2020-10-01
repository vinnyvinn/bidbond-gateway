<?php

namespace App\Jobs;

use App\Agent;
use App\Mail\BidbondReceipt;
use App\Services\BidBondService;
use App\Services\CompanyService;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class PostBidBond implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bidbond;

    protected $user;

    public function __construct($bidbond, $user)
    {
        $this->bidbond = $bidbond;
        $this->user = $user;
    }


    public function handle(CompanyService $companyService, BidBondService $bidBondService, PaymentService $paymentService)
    {
        if(!$this->bidbond->agent_id){
            $company = json_decode($companyService->getCompanyByUnique($this->bidbond->company_id));
        }else{
            $agent = Agent::secret($this->bidbond->agent_id)->first();
            $company = json_decode($bidBondService->getCompany($this->bidbond->company_id));
            $company->email = $agent->email;
            $company->customerid = $agent->customerid;
        }

        $payment = json_decode($paymentService->checkPayment('BP' . $this->bidbond->secret));
        Mail::to($this->user)->cc([$company->email])->later(now()->addMinute(),new BidbondReceipt($this->bidbond, $company, $payment));
        dispatch(new GenerateBidPdf($this->bidbond));
        if(!$this->bidbond->agent_id) {
            dispatch(new SetCommission($this->bidbond, $company));
        }
    }
}
