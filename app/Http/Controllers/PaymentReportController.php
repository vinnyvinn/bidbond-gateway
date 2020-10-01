<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\UsersExport;
use App\Exports\BidbondsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\BondPricingService;
use App\Services\CompanyService;
use App\Traits\ApiResponser;
use App\User;
use Carbon\Carbon;

class PaymentReportController extends Controller
{
    public $bondPricingService;
    public $companyService;
    use ApiResponser;

	public function __construct( BondPricingService $bondPricingService, CompanyService $companyService)
    {
        $this->bondPricingService = $bondPricingService;
        $this->companyService = $companyService;
    }

    public function list(Request $request) {


        $data = $this->bondPricingService->paymentReport();

        $data = json_decode($data,true);
        $newData = collect();
        foreach ($data as $value) {

            $company_details = json_decode($this->companyService->obtainCompany($value['company_id']), true);
            $newValue['name'] = $company_details['data']['name'];
            $newValue['currency'] = 'KES';
            $newValue['principal_amount'] = $value['amount'];
            $newValue['deal_date'] = $value['deal_date'];
            $newValue['maturity_days'] = $value['period'];
            $newValue['value_date'] = $value['effective_date'];
            $newValue['beneficity_name'] = $value['name'];
            $newValue['bid_bond_number'] = '';
            $newValue['QTRS'] = $this->qtrValue($value['period']);
            $newValue['COMMISSION'] = $this->commission($value['amount'],$newValue['QTRS']);
            $newValue['EXCISE_TAX'] = $newValue['COMMISSION']*0.2;
            $newValue['Work_day'] = ((Carbon::parse($value['deal_date'])->dayOfWeek == 1 || Carbon::parse($value['deal_date'])->dayOfWeek == 7)?0:1);
            $newValue['month'] = Carbon::parse($value['deal_date'])->format('m');
            $newValue['day'] = Carbon::parse($value['deal_date'])->format('d');
            $newValue['year'] = Carbon::parse($value['deal_date'])->format('Y');
            $newValue['NICV'] = $newValue['COMMISSION']*0;
            $newValue['TF'] = $newValue['COMMISSION']*0.8;
            $newValue['MPF'] = $newValue['COMMISSION']*0.2;
            $newValue['RM'] = '';
            $newValue['EXPIRE_DD'] = (string)Carbon::parse($value['effective_date'])->addDays($value['period']);
            $newValue['EXPIRED_AMT'] = ((Carbon::parse($newValue['EXPIRE_DD'])>now())?$newValue['principal_amount']:'');
            $newValue['BIDBOND_NO'] = '';
            $newValue['CUSTOMER'] = $newValue['name'];

            $newData = $newData->push($newValue);

        }

        return response()->json($newData);
    }

    public function index(Request $request) {

        $data = json_decode($this->bondPricingService->paymentReport(),true);

        $newData = collect();
        foreach ($data as $value) {
            // return $value;
            $company_details = json_decode($this->companyService->obtainCompany($value['company_id']), true);
            $newValue['name'] = $company_details['data']['name'];
            $newValue['currency'] = 'KES';
            $newValue['principal_amount'] = $value['amount'];
            $newValue['deal_date'] = $value['deal_date'];
            $newValue['maturity_days'] = $value['period'];
            $newValue['value_date'] = $value['effective_date'];
//            $newValue['beneficity_name'] = $value['name'];
            $newValue['bid_bond_number'] = '';
            $newValue['QTRS'] = $this->qtrValue($value['period']);
            $newValue['COMMISSION'] = $this->commission($value['amount'],$newValue['QTRS']);
            $newValue['EXCISE_TAX'] = $newValue['COMMISSION']*0.2;
            $newValue['Work_day'] = ((Carbon::parse($value['deal_date'])->dayOfWeek == 1 || Carbon::parse($value['deal_date'])->dayOfWeek == 7)?0:1);
            $newValue['month'] = Carbon::parse($value['deal_date'])->format('m');
            $newValue['day'] = Carbon::parse($value['deal_date'])->format('d');
            $newValue['year'] = Carbon::parse($value['deal_date'])->format('Y');
            $newValue['NICV'] = $newValue['COMMISSION']*0;
            $newValue['TF'] = $newValue['COMMISSION']*0.8;
            $newValue['MPF'] = $newValue['COMMISSION']*0.2;
            $newValue['RM'] = '';
            $newValue['EXPIRE_DD'] = (string)Carbon::parse($value['effective_date'])->addDays($value['period']);
            $newValue['EXPIRED_AMT'] = ((Carbon::parse($newValue['EXPIRE_DD'])>now())?$newValue['principal_amount']:'');
            $newValue['BIDBOND_NO'] = '';
            $newValue['CUSTOMER'] = $newValue['name'];

            $newData = $newData->push($newValue);

        }

         return $newData;
//        return Excel::download(new BidbondsExport($newData), rand().'.xlsx');
    }

    public function qtrValue($data) {

        $value = 5;

        if ($data/90 <= 1) {
            $value = 1;
        }elseif ($data/90 <= 2) {
            $value = 2;
        }elseif ($data/90 <= 3) {
            $value = 3;
        }elseif ($data/90 <=4) {
            $value = 4;
        }else{
            $value = 5;
        }

        return $value;
    }

    public function commission($pa,$qtr) {
        $value = $pa*$qtr*0.0007;

        if ($pa<100000) {
            $value = 1800;
        }elseif ($pa<500000) {
            $value = 1900*$qtr;
        }elseif ($pa<1000000) {
            $value = 3500*$qtr;
        }elseif ($pa<2000000) {
            $value = 0.006*$pa*$qtr;
        }

        return $value;
    }
}
