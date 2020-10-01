<?php


namespace App\Services;

use App\Interfaces\WalletPayableInterface;
use function GuzzleHttp\json_decode;

class AgentWalletService implements WalletPayableInterface
{
    public $secret;
    public $paymentService;

    /**
     * AgentWalletService constructor.
     * @param $secret
     * @param PaymentService $paymentService
     */
    public function __construct($secret, PaymentService $paymentService)
    {
        $this->secret = $secret;
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
            'type_id' => $this->secret,
            'user_id' => $data['user_id'],
            'user_name' => $data['user_name'],
            'account_type' => 'Agent',
        ]), true);
    }

    /**
     *
     * @return mixed
     */
    public function getBalance()
    {
        return json_decode($this->paymentService->getWalletBalance("Agent", $this->secret), true);
    }

}
