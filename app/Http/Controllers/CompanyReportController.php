<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CompanyService;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use App\Exports\BidbondsExport;
use Maatwebsite\Excel\Facades\Excel;

class CompanyReportController extends Controller
{
    public $companyService;
    use ApiResponser;

	public function __construct( CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function list(Request $request) {

        return $data = $this->companyService->obtainApprovedCompanies();

        $data = json_decode($data,true);
        $newData = collect();
        foreach ($data as $value) {

            $newValue['id'] = $value['id'];
            $newValue['name'] = $value['name'];
            $newValue['crp'] = $value['crp'];
            $newValue['email'] = $value['email'];
            $newValue['phone_number'] = $value['phone_number'];
            $newValue['physical_address'] = $value['physical_address'];
            $newValue['postal_address'] = $value['postal_address'];
            $newValue['postal_code_id'] = $value['postal_code_id'];
            $newValue['paid'] = $value['paid'];
            $newValue['approval_status'] = $value['approval_status'];
            $newValue['company_unique_id'] = $value['company_unique_id'];
            $newValue['created_at'] = $value['created_at'];
            $newValue['updated_at'] = $value['updated_at'];
            $newValue['RM'] = '';                 
            $newValue['day'] = Carbon::parse($value['created_at'])->format('d');  
            $newValue['month'] = Carbon::parse($value['created_at'])->format('m');   
            $newValue['year'] = Carbon::parse($value['created_at'])->format('Y');


            
            
            $newData = $newData->push($newValue);
            
        }

        return response()->json($newData);
    }

    public function index(Request $request) {

	   	$data = $this->companyService->obtainApprovedCompanies();

        $data = json_decode($data,true);
        $newData = collect();
        foreach ($data as $value) {
            // return $value;

            $newValue['id'] = $value['id'];
            $newValue['name'] = $value['name'];
            $newValue['crp'] = $value['crp'];
            $newValue['email'] = $value['email'];
            $newValue['phone_number'] = $value['phone_number'];
            $newValue['physical_address'] = $value['physical_address'];
            $newValue['postal_address'] = $value['postal_address'];
            $newValue['postal_code_id'] = $value['postal_code_id'];
            $newValue['paid'] = $value['paid'];
            $newValue['approval_status'] = $value['approval_status'];
            $newValue['company_unique_id'] = $value['company_unique_id'];
            $newValue['created_at'] = $value['created_at'];
            $newValue['updated_at'] = $value['updated_at'];
            $newValue['RM'] = '';                 
            $newValue['day'] = Carbon::parse($value['created_at'])->format('d');  
            $newValue['month'] = Carbon::parse($value['created_at'])->format('m');   
            $newValue['year'] = Carbon::parse($value['created_at'])->format('Y');


            
            
            $newData = $newData->push($newValue);
            
        }

        return $newData;

        

        return Excel::download(new BidbondsExport($newData), rand().'.xlsx');
    	
    }
}
