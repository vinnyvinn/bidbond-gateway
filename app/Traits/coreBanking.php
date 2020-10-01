<?php


namespace App\Traits;

use App\Services\BidBondService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\CompanyService;
use PHPUnit\Exception;

class coreBanking
{

    public static function initClient(){
     return new \SoapClient("http://10.0.1.27:3070/JBidBonds.asmx?wsdl");
    }
   public static function getSessionId(){
       try {
           $branch_id = '001';
           $bank_id = '51';
           $param = ['UInfoXML' => '&lt;UserInfo&gt;&lt;BranchID&gt;001&lt;/BranchID&gt;&lt;BankID&gt;51&lt;/BankID&gt;&lt;UserName&gt;JBID&lt;/UserName&gt;&lt;Password&gt;s+6DLmoJ+EiFhUfnBYFGRAt+p66ePUzi5CAoGqspsMo=&lt;/Password&gt;&lt;/UserInfo&gt;'];
           $response = self::initClient()->RequestConnectionSession($param);
           $array = json_decode(json_encode($response),true);
           $session='';
           $xml = simplexml_load_string($array['RequestConnectionSessionResult']['DS_Details']);
           foreach ($xml as $ml){
            $session = $ml->SessionID;
           }
           return (string)$session;
       }catch (\Exception $e){
           echo $e->getMessage();
       }
   }

 public static function getAccountDetails($account_no='0011762858001'){
        $data = [
         'SessionID' => self::getSessionId(),
         'UniqueID' => Str::Random(9),
         //'AccountID' => '0011762858001'
        'AccountID' => $account_no
     ];

     $array = ['AccountDetails' =>$data];
     try {
        return $response =  self::initClient()->GetAccountDetails($array);
         dd($response);
     }catch (\Exception $e){
         echo $e->getMessage();
     }
 }
public static function registerNewAccount($company='YOJ61'){
    $details = json_decode(CompanyService::initCompany()->obtainCompany($company),true);
    $identity = 'national id';
    if (isset($details['data']['director_details']['id_type'])){
        $identity = $details['data']['director_details']['id_type'] =='Citizen' ? 'national id' :'passport';
    }

    $inserted_data = [
        'SessionID' => self::getSessionId(),
        'UniqueID' =>Str::Random(9),
        'ClientTypeID' => 'C',
        'ProductID' => 'JCC',
        'AccountOfficerID' => '1762858',
        'CountryID' => 'KE',
        'CityID' =>'',
        'BusinessOwnershipID' =>'',
        'EmailID' => $details['data']['email'],
        'Address' => $details['data']['company_details']['physical_address'],
        'AddressTypeID' => 'B',
        'Phone1' => isset($details['data']['company_details']['phone_number']) ? $details['data']['company_details']['phone_number'] : $details['data']['phone_number'] ,
        'KRAPin' => isset($details['data']['company_details']['kra_pin']) ? $details['data']['company_details']['kra_pin'] : $details['data']['kra_pin'],
        'Remarks' => '',
        'CorporateCustomer' => [
            'CompanyName' => isset($details['data']['company_details']['business_name']) ? $details['data']['company_details']['business_name'] : $details['data']['name'] ,
            'RegistrationDate' => isset($details['data']['company_details']['registration_date']) ? $details['data']['company_details']['registration_date'] : $details['data']['created_at'],
            'RegistrationNumber' => isset($details['data']['company_details']['registration_number']) ? $details['data']['company_details']['registration_number'] : $details['data']['crp'],
            'RegisteredAt' => isset($details['data']['company_details']['physical_address']) ? $details['data']['company_details']['physical_address'] : $details['data']['physical_address'],
            'RegisteredOffice' => isset($details['data']['company_details']['physical_address']) ? $details['data']['company_details']['physical_address'] : $details['data']['physical_address'],
            'BusinessDescription' => isset($details['data']['company_details']['registration_number']) ? $details['data']['company_details']['registration_number'] : $details['data']['crp'],
            'Website' => '',
            'IdentificationTypeID' =>$identity,
            'BusinessLineID' => '',
            'BusinessOwnership' =>''
        ],
        'IndividualCustomer' => [
            'TitleID' => '',
            'FirstName' => '',
            'LastName' => '',
            'MiddleName' => '',
            'GenderID' => '',
            'NationalityID' => '',
            'DateOfBirth' => '',
            'ResidentID' =>'',
            'IdentificationNo' => '',
            'IdentificationExpiryDate' =>'',
            'IdentificationTypeID' =>'',
        ]
    ];
   $account_data = ['CustomerDetails' => $inserted_data];
    try {
        $response = self::initClient()->RegisterCustomer($account_data);
        $array = json_decode(json_encode($response),true);
        $customer_id='';
        $account_id='';
        $xml = simplexml_load_string($array['RegisterCustomerResult']['DS_Details']);
        foreach ($xml as $ml){
            $customer_id = (string)$ml->ClientID;
            $account_id = (string)$ml->AccountID;
        }
        if ($account_id==''){
            return response()->json('account_exists');
        }
       return response()->json(['account'=>$account_id,'customer_id'=>$customer_id]);
    }catch (\Exception $e){
        echo $e->getMessage();
    }
}
public static function applyBidbond($company,$tender_no,$total,$user,$role){
    $details = [];
    if ($role=='agent'){
        $details = User::find($user)->agent->first();
    }else{
     $details = json_decode(CompanyService::initCompany()->obtainCompany($company),true);
    }
  $tender = json_decode(BidBondService::init()->getTenderInfo(['tender_number'=>$tender_no]),true);
    $data = [
     'SessionID' => self::getSessionId(),
     'UniqueID' => Str::Random(9),
     'OurBranchID' =>'001',
     'ClientID' => isset($details->customerid) ? $details->customerid : $details['data']['customerid'],
     'ClientName' =>isset($details->name) ? $details->name : $details['data']['name'],
     'AccountID' => isset($details->account) ? $details->account : $details['data']['account'],
      'ProductID' =>'LG05',
      'CurrencyID' =>'KES',
      'Amount' =>$total,
      'ExpiryDate'=>$tender['data']['expiry_date'],
      'TenderNumber'=>$tender['data']['tender_no'],
      'TenderDate'=>$tender['data']['effective_date'],
      'ChargeAccountID'=>isset($details->account) ? $details->account : $details['data']['account'],
      'ValueDate'=>$tender['data']['expiry_date'],
      'Remarks'=>'',
      'ApplicationDate'=>Carbon::parse($tender['data']['created_at'])->format('Y-m-d'),
      'AccountOfficerID'=>'1762858'
    ];
    $app_data = ['BidBondApplication' => $data];
    //dd($app_data);
   try {
    $response = self::initClient()->BidBondApplication($app_data);
    $array = json_decode(json_encode($response),true);
       $xml = simplexml_load_string($array['BidBondApplicationResult']['DS_Details']);
       $application_id ='';
       foreach ($xml as $ml){
        $application_id = (string)$ml->ApplicationID;
       }
       info('Application Id => '.$application_id);
     return $application_id;
    }catch (\Exception $e){
      echo $e->getMessage();
    }
}

public static function accountBalance($account_no){
    $response = self::getAccountDetails($account_no);
    $array = json_decode(json_encode($response),true);
    $xml = simplexml_load_string($array['GetAccountDetailsResult']['DS_Details']);
    $balance ='';
    foreach ($xml as $ml){
        $balance = $ml->ClearBalance;
       // $balance = $ml->AvailableBalance;
    }
    info('bal => '.(string)$balance);
    return (string)$balance;
}

public static function invokeAccount($user,$role){
     $account = '';
      if ($role=='agent'){
        $account = User::find($user)->agent->first()->account;
    }else{
      $company = CompanyUser::getCompaniesForUser($user);
      $account = isset($company[0]['account']) ? $company[0]['account'] : '';
    }
    if ($account){
      return self::accountBalance($account);
    }
    return 0;
}
}
