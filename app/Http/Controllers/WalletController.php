<?php

namespace App\Http\Controllers;

use App\Services\CompanyService;
use App\Services\PaymentService;
use App\Services\WalletFactory;
use App\Traits\WalletTrait;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    use WalletTrait;

    public $paymentService;
    public $companyService;
    public $walletFactory;

    public function __construct(PaymentService $paymentService, CompanyService $companyService, WalletFactory $walletFactory)
    {
        $this->paymentService = $paymentService;
        $this->companyService = $companyService;
        $this->walletFactory = $walletFactory;
    }


    public function transactions($type, $type_id)
    {
        return response()->json(json_decode($this->paymentService->getWalletStatement($type, $type_id), true));
    }

    public function getByUser()
    {
        $user = auth()->user();

        if (!$user->isAn('customer')) {
            abort('Only customers can fetch their company wallets', 403);
        }

        $companies = collect(json_decode($this->companyService->obtainUserCompanies(['email' => $user->email, 'userid' => $user->id]), true));

        $wallets = collect(
            json_decode(
                $this->paymentService->getWallets([
                    'type' => 'Company',
                    'type_id' => $companies->pluck('company_unique_id')->all()
                ]),
                true)['wallets']
        );

        $wallets = $wallets->map(function ($wallet) use ($companies) {
            $wallet['company'] = $companies->firstWhere('company_unique_id', $wallet['type_id'])['name'];
            return $wallet;
        });

        return response()->json($wallets);
    }

    public function balance(Request $request)
    {
        $user = auth()->user();

        if (!$user->isAn('agent') && !$user->isAn('customer')) {
            return response()->json('Only customers and agents can check wallet balance', 400);
        }

        if ($user->isAn('customer')) {
         info($request->all());
         info('company----');
            $walletService = $this->walletFactory->initializePayment('company', $request->company);

            $wallet_balance = $this->userCompanyWalletBalance($this->companyService, $walletService);
        }

        if ($user->isAn('agent')) {

            $agent = auth()->user()->agent()->first();

            $walletService = $this->walletFactory->initializePayment('agent', $agent->secret);

            $wallet_balance = $this->agentWalletBalance($request->company, $walletService);
        }

        if ($wallet_balance['status'] === 'error') {
            return response()->json($wallet_balance, 400);
        }

        return response()->json($wallet_balance);
    }

}
