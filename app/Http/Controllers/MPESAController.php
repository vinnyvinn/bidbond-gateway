<?php

namespace App\Http\Controllers;

use App\Jobs\CompanySearchPaid;
use App\PriceSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Traits\ApiResponser;
use App\Services\BidBondService;
use Illuminate\Support\Facades\Log;
use App\Services\CompanyService;
use function GuzzleHttp\json_decode;
use Illuminate\Support\Facades\Validator;

class MPESAController extends Controller
{

    use ApiResponser;

    public $paymentService;
    public $bidBondService;
    public $companyService;

    public function __construct(PaymentService $paymentService, BidBondService $bidBondService, CompanyService $companyService)
    {
        $this->paymentService = $paymentService;
        $this->companyService = $companyService;
        $this->bidBondService = $bidBondService;
    }

    public function companyRegistrationDetails()
    {
        return $this->successResponse([
            'amount' => PriceSetting::option(PriceSetting::cr12_search_cost)->first()->value,
            'account' => auth()->user()->account_number
        ]);
    }

    public function paymentRequest(Request $request)
    {
        $data = $this->validate($request, [
            'phone' => 'bail|required|phone',
            'amount' => 'bail|required|number_format',
            'account' => 'required',
            'real_account' => 'required'
        ]);
        return $this->paymentService->paymentRequest($data);
    }

    public function confirmPayment(Request $request)
    {
        $account = $request->account;
        $account_prefix = substr($account, 0, 2);
        if ($account_prefix !== 'CP') {
            return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
        }
        $expected_amount = PriceSetting::option(PriceSetting::cr12_search_cost)->first()->value;

        if (!$request->exists('expected_amount')) {
            $request->request->add(['expected_amount' => $expected_amount]);
        }

        $response = json_decode($this->paymentService->confirmPayment($request->all()));

        if ($response->confirmed == true) {
            if ($response->total_paid < $expected_amount) {
                return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
            }
            $company = json_decode($this->companyService->updatePaymentStatus(['account' => substr($account, 2)]));
            dispatch(new CompanySearchPaid($account, auth()->user(),$company));
        }
        return response()->json($response);
    }

    public function confirmTransaction(Request $request)
    {
        $data = $this->validate($request, [
            'account' => 'required',
            'transaction_code' => 'required',
        ]);
        $account = $request->account;
        $account_prefix = substr($account, 0, 2);
        if ($account_prefix !== 'CP') {
            return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
        }
        $data['real_account'] = $request->real_account;
        $response = json_decode($this->paymentService->confirmTransaction($data), true);
        if ($response['confirmed'] == true) {
            $company_search_cost = PriceSetting::option(PriceSetting::cr12_search_cost)->first()->value;
            if ($response['total_paid'] < $company_search_cost) {
                return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
            }
            $company = json_decode($this->companyService->updatePaymentStatus(['account' => substr($account, 2)]));
            dispatch(new CompanySearchPaid($account, auth()->user(),$company));
        }
        return response()->json($response);
    }

    public function c2bValidation(Request $request, $secret)
    {
        if ($secret != config('mpesa.payment_secret')) {
            Log::error("invalid mpesa c2bValidation request attempt: " . $request->ip());
            return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
        }

        $validator = Validator::make($request->all(), [
            "TransID" => "required",
            "TransTime" => "required|date_format:YmdHis",
            "TransAmount" => "required",
            "BusinessShortCode" => "required|in:" . config('mpesa.short_code'),
            "BillRefNumber" => "required",
            "MSISDN" => "required",
            "FirstName" => "required",
            "MiddleName" => "sometimes",
            "LastName" => "sometimes"
        ]);

        if ($validator->fails()) {
            info($validator->errors()->first());
            return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
        }

        $data = $request->all();
        $account = strtoupper(preg_replace('/\s+/', '', $data['BillRefNumber']));
        $account_prefix = substr($account, 0, 2);

        if ($account_prefix !== 'CP') {
            return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
        }
        $data['expected_amount'] = PriceSetting::option(PriceSetting::cr12_search_cost)->first()->value;
        if ($data['TransAmount'] != $data['expected_amount']) {
            return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
        }
        return $this->companyPayment($account, $data);
    }

    public function c2bConfirmation(Request $request, $secret)
    {
        if ($secret != config('mpesa.payment_secret')) {
            Log::error("invalid mpesa c2bConfirmation request attempt: " . $request->ip());
            return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
        }
        $validator = Validator::make($request->all(), [
            "TransID" => "required",
            "TransTime" => "required|date_format:YmdHis",
            "TransAmount" => "required",
            "BusinessShortCode" => "required|in:". config('mpesa.short_code'),
            "BillRefNumber" => "required",
            "MSISDN" => "required",
            "FirstName" => "required",
            "MiddleName" => "sometimes",
            "LastName" => "sometimes"
        ]);
        if ($validator->fails()) {
            return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
        }
        $data = $request->all();
        Log::info('C2B Confirmation: ' . request()->ip());
        Log::info($data);
        //fetch payments for account
        $response = json_decode($this->paymentService->c2bConfirmation($request->all()), true);
        Log::info("c2bConfirmation response " . print_r($response, true));
        return response()->json($response);
    }

    public function stkConfirmation(Request $request, $secret)
    {
        if ($secret != config('mpesa.payment_secret')) {
            Log::error("invalid mpesa stkConfirmation request attempt: " . $request->ip());
            return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
        }
        $response = $this->paymentService->stkConfirmation($request->all());
        return response()->json(json_decode($response));
    }

    public function trxStatusConfirmation(Request $request, $secret)
    {
        if ($secret != config('mpesa.payment_secret')) {
            Log::error("invalid mpesa trxStatusConfirmation request attempt: " . $request->ip());
            return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
        }
        $response = $this->paymentService->trxStatusConfirmation($request->all());
        return response()->json(json_decode($response));
    }

    public function trxStatusTimeout(Request $request, $secret)
    {
        if ($secret != config('mpesa.payment_secret')) {
            Log::error("invalid mpesa trxStatusTimeout request attempt: " . $request->ip());
            return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
        }
        $response = $this->paymentService->trxStatusTimeout($request->all());
        return response()->json(json_decode($response));
    }


    protected function companyPayment(string $account, array $data): JsonResponse
    {
        $company_details = json_decode($this->companyService->obtainCompany(substr($account, 2)));
        if (isset($company_details->code)) {
            return response()->json(["ResultCode" => 1, "ResultDesc" => "Failure"]);
        }
        return response()->json(
            json_decode($this->paymentService->c2bValidation(array_merge($data, [
                'type' => 'Company',
                'type_id' => $company_details->data->company_unique_id
            ]), true))
        );
    }

    protected function agentPayment(string $account, array $data): JsonResponse
    {
        return response()->json(
            json_decode($this->paymentService->c2bValidation(array_merge($data, [
                'type' => 'Agent',
                'type_id' => $account
            ]), true))
        );
    }

}
