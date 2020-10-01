<?php


namespace App\Services;


use App\Interfaces\WalletPayableInterface;
use function GuzzleHttp\json_decode;

class CompanyWalletService implements WalletPayableInterface
{
    public $company_id;
    public $paymentService;
    protected $type = "Company";

    /**
     * AgentWalletService constructor.
     * @param $company_id
     * @param PaymentService $paymentService
     */
    public function __construct($company_id, PaymentService $paymentService)
    {
        $this->company_id = $company_id;
        $this->paymentService = $paymentService;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function pay($data)
    {
        return json_decode($this->paymentService->paywithwallet([
            'amount' => $data['amount'],
            'account' => $data['account'],
            'type' => 'Bidbond',
            'type_id' => $this->company_id,
            'user_id' => $data['user_id'],
            'user_name' => $data['user_name'],
            'account_type' => $this->type,
        ]), true);
    }

    /**
     *
     * @return array
     */
    public function getBalance()
    {
        return json_decode($this->paymentService->getWalletBalance($this->type, $this->company_id), true);
    }
}
