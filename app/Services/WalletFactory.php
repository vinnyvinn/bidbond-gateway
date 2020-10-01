<?php


namespace App\Services;


use Exception;

class WalletFactory
{
    public $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @param $type
     * @param $type_id
     * @return AgentWalletService|CompanyWalletService
     * @throws Exception
     */
    public function initializePayment($type, $type_id)
    {
        if ($type == "agent") {
            return new AgentWalletService($type_id,$this->paymentService);
        } elseif ($type == "company") {
            return new CompanyWalletService($type_id,$this->paymentService);
        }
        throw new Exception("Unsupported Payment method");
    }
}
