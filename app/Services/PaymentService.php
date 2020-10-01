<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;

class PaymentService
{

    use ConsumesExternalService;

    public $baseUri;

    public $secret;


    public function __construct()
    {
        $this->baseUri = config('services.payments.base_uri');
        $this->secret = config('services.payments.secret');
    }

    public function c2bValidation($data)
    {
        return $this->performRequest('POST', '/api/safaricom/c2b/validation/callback/'. config('mpesa.payment_secret'), $data);
    }

    public function c2bConfirmation($data)
    {
        return $this->performRequest('POST', '/api/safaricom/c2b/confirmation/callback/'. config('mpesa.payment_secret'), $data);
    }

    public function trxStatusConfirmation($data)
    {
        return $this->performRequest('POST', '/api/safaricom/trx-status/confirmation/callback/'. config('mpesa.payment_secret'), $data);
    }

    public function trxStatusTimeout($data)
    {
        return $this->performRequest('POST', '/api/safaricom/trx-status/timeout/callback/'. config('mpesa.payment_secret'), $data);
    }


    public function paymentRequest($data)
    {
        return $this->performRequest('POST', '/api/safaricom/requestPayment', $data);
    }

    public function setPaid($account)
    {
        return $this->performRequest('POST', '/api/payment/paid', ["account" => $account]);
    }

    public function checkPayment($account)
    {
        return $this->performRequest('get', '/api/payment/'. $account .'/account');
    }

    public function getWalletBalance($type, $type_id)
    {
        return $this->performRequest('get', '/api/wallet/balance/' . $type . '/' . $type_id);
    }

    public function getTransactions($data)
    {
        /*
         * @params
         *  company_ids // array of company ids eg ['1211212121'], ['1211212121', '23232323232', '23242323232']
         *  type  // type of report eg Payments,Wallet,Both
         *  from // optional from date --format Y-m-d
         *  to // optional to date --format Y-m-d
         */
        return $this->performRequest('get', '/api/reports/transactions', $data);
    }

    public function payWithWallet($data)
    {
        return $this->performRequest('post', '/api/wallet/pay', $data);
    }

    public function payWithWalletAtm($data)
    {
        return $this->performRequest('post', '/api/wallet/pay/atm', $data);
    }

    public function stkConfirmation($data)
    {
        return $this->performRequest('post', '/api/safaricom/stk/confirmation/callback/'. config('mpesa.payment_secret'), $data);
    }

    public function confirmPayment($data)
    {
        return $this->performRequest('post', '/api/safaricom/confirmPayment', $data);
    }

    public function confirmTransaction($data)
    {
        return $this->performRequest('post', '/api/safaricom/confirmTransaction', $data);
    }

    public function getWalletStatement($type, $type_id)
    {
        return $this->performRequest('get', '/api/wallet/' . $type . '/' . $type_id . '/transactions');
    }

    public function getWalletStatements()
    {
        return $this->performRequest('get', '/api/wallet/transactions');
    }

    public function getWallets($data)
    {
        return $this->performRequest('post', '/api/wallet/balance', $data);
    }

    public function getPaymentsByAccounts($data)
    {
        return $this->performRequest('get', '/api/payments/account', $data);
    }

    public function getPayments($data)
    {
        return $this->performRequest('get', '/api/payments', $data);
    }

    public function migrate()
    {
        return $this->performRequest('get', '/api/migrate');
    }
}
