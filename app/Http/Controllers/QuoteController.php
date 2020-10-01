<?php

namespace App\Http\Controllers;

use App\BidbondPrice;
use App\Group;
use App\Mail\SendQuote;
use App\Quote;
use Bouncer;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Mail;
use App\Services\BidBondService;
use App\Services\CounterPartyService;
use Auth;

class QuoteController extends Controller
{
    public $bidBondService;

    public $counterpartyService;

    use ApiResponser;

    public function __construct(BidBondService $bidBondService, CounterPartyService $counterpartyService)
    {
        $this->bidBondService = $bidBondService;

        $this->counterpartyService = $counterpartyService;
    }

    public function index()
    {
        if (Bouncer::can('list-quotes')) {

            return $this->successResponse(Quote::paginate());

        } else if (Bouncer::can('list-quotes-owned')) {

            $user = Auth::guard('api')->user();
            $quotes = Quote::where('email', $user->email)->paginate();

            return $this->successResponse($quotes);
        }
    }


    public function postQuote(Request $request)
    {
        $data = $request->validate([
            'email' => 'bail|required|email',
            'phone' => 'nullable|numeric',
            'amount' => 'bail|required|numeric',
            'tenure' => 'bail|required|in:30,60,90,120,150,180,210,365',
            'counterparty' => 'required'
        ]);
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->isAn('agent')) {
                $group_id = $user->agents()->group_id;
            } else {
                $group_id = Group::ofName('customer')->first()->id;
            }

        } else {
            $group_id = Group::ofName('customer')->first()->id;
        }

        $bidbond_breakdown = BidbondPrice::getBreakdown($request->amount, $request->tenure, $group_id);

        $data['charge'] = $bidbond_breakdown['total'];

        Quote::create($data);

        return $this->successResponse($bidbond_breakdown, 200);

    }

    public function sendQuote(Request $request)
    {
        $request->validate([
            'email' => 'bail|required|email',
            'amount' => 'bail|required|numeric',
            'tenure' => 'required',
            'counterparty' => 'required'
        ]);

        $party = json_decode($this->counterpartyService->show($request->counterparty), true);

        if (auth()->check()) {
            $user = auth()->user();

            if ($user->isAn('agent')) {
                $group_id = $user->agents()->group_id;
            } else {
                $group_id = Group::ofName('customer')->first()->id;
            }

        } else {
            $group_id = Group::ofName('customer')->first()->id;
        }

        $bidbond_breakdown = BidbondPrice::getBreakdown($request->amount, $request->tenure, $group_id);

        Mail::to($request->email)->queue(
            new SendQuote(
                auth()->user(),
                $request->amount,
                $bidbond_breakdown['bid_bond_charge'],
                $request->tenure,
                $bidbond_breakdown['excise_duty'],
                $bidbond_breakdown['total'],
                $party['data']['name'],
                $bidbond_breakdown['indemnity_cost'])
        );

        return $this->successResponse('A quote has been sent to the email ' . $request->email);
    }

    public function getQuote($id)
    {
        $data = Quote::where('counterparty', $id)->first();

        return response()->json($data, 200);
    }
}
