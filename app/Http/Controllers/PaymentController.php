<?php

namespace App\Http\Controllers;

use App\BidbondPrice;
use App\Jobs\GenerateBidPdf;
use App\Jobs\PaymentProcessed;
use App\Jobs\PostBidBond;
use App\Jobs\SetCommission;
use App\Services\WalletFactory;
use App\Traits\coreBanking;
use App\Traits\ProcessBond;
use App\Traits\WalletTrait;
use App\User;
use function GuzzleHttp\json_decode;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Traits\ApiResponser;
use App\Services\BidBondService;
use App\Services\CompanyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Bouncer;

class PaymentController extends Controller
{
    use ApiResponser, ProcessBond, WalletTrait;

    public $paymentService;
    public $bidBondService;
    public $companyService;

    public function __construct(PaymentService $paymentService, BidBondService $bidBondService, CompanyService $companyService)
    {
        $this->paymentService = $paymentService;
        $this->bidBondService = $bidBondService;
        $this->companyService = $companyService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        if (Bouncer::can('list-payments')) {

            return response()->json(json_decode($this->paymentService->getPayments($request->all()), true));

        } else if ($user->isAn('agent')) {
            $agent = $user->agent->first();

            $bidbonds = collect(json_decode($this->bidBondService->obtainBidBondsByAgent(['agent_id' => $agent->secret]), true)['data']);

        } else if (Bouncer::can('list-payments-owned')) {

            $user_ids = [$user->id];

            $bidbonds = collect(json_decode($this->bidBondService->obtainBidBondsByUser(compact('user_ids')), true)['data']);

        } else {
            return response()->json([]);
        }

        if ($bidbonds->count() == 0) {
            return response()->json([]);
        }

        return response()->json(
            collect(json_decode($this->paymentService->getPaymentsByAccounts(['payable_ids' => $bidbonds->pluck('bidbond_secret')->toArray()]), true))
        );
    }

    public function payFromAtm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total' => 'required|numeric',
            'currency' => 'required',
            'company' => 'required',
            'bidbond' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => ['message' => $validator->errors()->all()]
            ], 422);
        }
        $data = $request->all();
        $reference = coreBanking::applyBidbond($request->get('company'), $request->get('tender_no'), $request->get('total'),
            $request->get('user'), $request->get('role'));
        if (!$reference) {
            return response()->json([
                'status' => 'error',
                'error' => ['message' => "Something went wrong. Please contact system administrator for further assistance"]
            ], 400);
        }
        //update reference
        $bidbond = json_decode($this->bidBondService->applyBid(['reference' => $reference, 'secret' => $request->bidbond]));
        $user = User::find($request->user);
        if ($request->role == 'agent') {
            $account = $user->agent->first()->account;
        } else {
            $account = json_decode($this->companyService->getCompanyByUnique($request->get('company')))->account;
        }
        $data['reference'] = $reference;
        $data['account'] = $account;
        $data['user_name'] = $user->firstname . ' ' . $user->lastname;

        $this->paymentService->payWithWalletAtm($data);

        dispatch(new PostBidBond($bidbond, $user));
        return response()->json(['status' => 'success', 'message' => 'Wallet transaction successful']);
    }

}


