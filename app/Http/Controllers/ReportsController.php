<?php

namespace App\Http\Controllers;

use App\Quote;
use App\Services\ReportsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
Use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponser;
use Silber\Bouncer\Bouncer;

class ReportsController extends Controller
{
    use ApiResponser;

    public function getQuotes(){
        return response()->json(Quote::all());
    }
    public function getCompanies(){

      $reports = json_decode(ReportsService::initService()->companies(),true);
      return response($reports);
    }
    public function getBidbonds(){
      $reports = json_decode(ReportsService::initService()->bidbonds(),true);
      return response($reports);
    }
    public $reportsService;

    public function __construct(ReportsService $reportsService)
    {
        $this->reportsService = $reportsService;
    }
    public function getDashboard()
    {
        $reports = json_decode(ReportsService::initService()->dashboard(), true);
        return response($reports);
    }

    public function getAgentsBidbonds()
    {
        $user = Auth::guard('api')->user();

        if (Bouncer::can('list-agents-reports')) {
            $agents = DB::table('assigned_roles')
                ->join('users', 'users.id', 'assigned_roles.entity_id')
                ->join('roles', 'roles.id', 'assigned_roles.role_id')
                ->join('users', 'users.id', '=', 'agents.user_id')
                ->where('roles.name', ['agent'])
                ->get();

            $count = DB::table('assigned_roles')
                ->join('users', 'users.id', 'assigned_roles.entity_id')
                ->join('roles', 'roles.id', 'assigned_roles.role_id')
                ->join('users', 'users.id', '=', 'agents.user_id')
                ->join('users', 'users.id', '=', 'company_user.user_id')
                ->join('tenders', 'tenders.company_id', '=', 'company_user.company_id')
                ->where('roles.name', ['agent'])
                ->count();

            $agents['no_of_bids'] = $count;

            return $this->successResponse($agents);

        } else if (Bouncer::can('list-agents-owned-reports')) {

            $agents = DB::table('assigned_roles')
                ->join('users', 'users.id', 'assigned_roles.entity_id')
                ->join('roles', 'roles.id', 'assigned_roles.role_id')
                ->join('users', 'users.id', '=', 'agents.user_id')
                ->where('roles.name', ['agent'])
                ->where('agents.created_by', $user->id)
                ->get();

            $count = DB::table('assigned_roles')
                ->join('users', 'users.id', 'assigned_roles.entity_id')
                ->join('roles', 'roles.id', 'assigned_roles.role_id')
                ->join('users', 'users.id', '=', 'agents.user_id')
                ->join('users', 'users.id', '=', 'company_user.user_id')
                ->join('tenders', 'tenders.company_id', '=', 'company_user.company_id')
                ->where('roles.name', ['agent'])
                ->where('agents.created_by', $user->id)
                ->count();

            $agents['no_of_bids'] = $count;

            return $this->successResponse($agents);
        }
    }

    public function t24Report()
    {
        $bidbonds = Bidbond::join('tenders', 'bidbonds.tender_id', '=', 'tenders.id')
            ->join('companies', 'tenders.company_id', '=', 'companies.id')
            ->join('counterparties', 'tenders.counterparty_id', '=', 'counterparties.id')
            ->select('bidbonds.ref AS bidref', 'bidbonds.excise_duty AS duty', 'bidbonds.transaction_number AS tran', 'tenders.number', 'companies.name AS companyname', 'companies.id', 'counterparties.name AS counterpartyname', 'counterparties.id', 'tenders.amount', 'tenders.addressee', 'tenders.expiry_date', 'tenders.tenure_period', 'tenders.effective_date', DB::raw('DATE_FORMAT(bidbonds.created_at,"%Y-%m-%d") as date'))->get();

        return $this->successResponse($bidbonds);
    }

    public function bidbondSummary(Request $request)
    {
        return response()->json(json_decode($this->reportsService->bidbond_summary($request->all()), true));
    }

    public function expiredBidbonds(Request $request)
    {
        return response()->json(json_decode($this->reportsService->expired_bidbonds($request->all()), true));
    }

    public function companySummary(Request $request)
    {
        return response()->json(json_decode($this->reportsService->company_summary($request->all()),true));
    }

}
