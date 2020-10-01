<?php
namespace App\Services;

use Carbon\Carbon;
use Exception;
use SoapClient;

class JamiiCoreBanking
{
    protected $username;
    protected $password;
    protected $branch_id;
    protected $bank_id;
    protected $session_id;
    protected $auth_count;
    protected $account_officer_id;


    public function __construct()
    {
        $this->username = config('services.jamii.username'); //from config
        $this->password = config('services.jamii.password'); //from config
        $this->branch_id = config('services.jamii.branch_id'); //from config
        $this->bank_id = config('services.jamii.bank_id'); //from config
        $this->auth_count = config('services.jamii.auth_count'); //from config
        $this->account_officer_id = config('services.jamii.account_officer_id'); //from config

        $this->getSessionID();
    }

    public function getSessionID($cache = true)
    {
        if($cache){
            $session_id = ''; //get value from cache
            $this->session_id = $session_id;
        }

        $this->auth_count += 1;

        $client = $this->initializeClient();

        $params = ['UserInfo' => [
                'BranchID' => $this->branch_id,
                'BankID' => $this->bank_id,
                'UserName' => $this->username,
                'Password' => $this->password
            ]
        ];

        $params = $this->prepareParams($params);

        $response = $this->processResponse($client->RequestConnectionSessionResult($params), 'RequestConnectionSessionResponse');

        if($response['status'] === 'success'){
            $this->session_id = $response['data'];
        } else {
            // unable to generate session
        }
    }

    public function getAccount($account_id)
    {
        $client = $this->initializeClient();

        $params = ['AccountDetails' => [
                'SessionID' => $this->session_id,
                'UniqueID' => $this->getUniqueID(),
                'AccountID' => $account_id
            ]
        ];

        $params = $this->prepareParams($params);

        $response = $this->processResponse($client->GetAccountDetails($params), 'GetAccountDetailsResult');

        if($response['status'] === 'success'){
            return response()->json([
                'status' => 'success',
                'data' => [

                ]//construct response from $response['data']
            ]);
        }

        return response()->json([
            'status' => 'error',
            'error' => $response['error_message']
        ]);

    }

    public function registerCustomer($customer)
    {

        $client = $this->initializeClient();

        $params = ['CustomerDetails' => [
                'SessionID' => $this->session_id,
                'UniqueID' => $this->getUniqueID(),
                'ClientTypeID' => $customer['client_type'],
                'ProductID' => $customer['product_id'],
                'AccountOfficerID' => $this->account_officer_id,
                'CountryID' => $customer['country_id'],
                'EmailID' => $customer['email'],
                'Address' => $customer['address'],
                'Phone1' => $customer['phone'],
                'KRAPin' => $customer['kra_pin'],
                'Remarks' => $customer['remarks'],
                'IndividualCustomer' => [
                    'TitleID' => $customer['title'],
                    'FirstName' => $customer['first_name'],
                    'LastName' => $customer['last_name'],
                    'MiddleName' => $customer['middle_name'],
                    'GenderID' => $customer['gender_id'],
                    'NationalityID' => $customer['nationality_id'],
                    'DateOfBirth' => $customer['date_of_birth'],
                    'ResidentID' => $customer['resident_id'],
                    'IdentificationNo' => $customer['identification_no'],
                    'IdentificationIssuedCityID' => $customer['identification_issued_city_id'],
                    'IdentificationExpiryDate' => $customer['identification_expiry_date'],
                    'IdentificationTypeID' => $customer['identification_type_id'],
                ],
                'CorporateCustomer' => [
                    'CompanyName' => $customer['company_name'],
                    'RegistrationDate' => $customer['registration_date'],
                    'RegistrationNumber' => $customer['registration_number'],
                    'RegisteredAt' => $customer['registered_at'],
                    'RegisteredOffice' => $customer['registered_office'],
                    'BusinessDescription' => $customer['business_description'],
                    'Website' => '',
                    'IdentificationTypeID' => $customer['identification_type_id'],
                    'BusinessLineID' => $customer['business_line_id'],
                    'BusinessOwnership' => $customer['business_ownership'],
                ]
            ]
        ];

        $params = $this->prepareParams($params);

        $response = $this->processResponse($client->GetAccountDetails($params), 'GetAccountDetailsResult');

        if($response['status'] === 'success'){
            return response()->json([
                'status' => 'success',
                'data' => [

                ]//construct response from $response['data']
            ]);
        }

        return response()->json([
            'status' => 'error',
            'error' => $response['error_message']
        ]);

    }

    public function applyBidBond($bidbond)
    {
        $client = $this->initializeClient();

        $params = ['BidBondApplication' => [
                'SessionID' => $this->session_id,
                'UniqueID' => $this->getUniqueID(),
                'OurBranchID' => $this->branch_id,
                'ClientID' => $bidbond['client_id'],
                'ClientName' => $bidbond['client_name'],
                'AccountID' => $bidbond['account_id'],
                'ProductID' => $bidbond['product_id'],
                'CurrencyID' => $bidbond['currency_id'],
                'Amount' => $bidbond['amount'],
                'ExpiryDate' => $bidbond['expiry_date'],
                'TenderNumber' => $bidbond['tender_number'],
                'TenderDate' => $bidbond['tender_date'],
                'ChargeAccountID' => $bidbond['charge_account_id'],
                'ValueDate' => $bidbond['value_date'],
                'Remarks' => $bidbond['remarks'],
                'ApplicationDate' => Carbon::now(),
                'AccountOfficerID' => $this->account_officer_id,
            ]
        ];

        $params = $this->prepareParams($params);

        $response = $this->processResponse($client->GetAccountDetails($params), 'GetAccountDetailsResult');

        if($response['status'] === 'success'){
            return response()->json([
                'status' => 'success',
                'data' => [

                ]//construct response from $response['data']
            ]);
        }

        return response()->json([
            'status' => 'error',
            'error' => $response['error_message']
        ]);

    }

    protected function prepareParams($params){
        return json_decode(json_encode($params));
    }

    protected function initializeClient(){
        try {
            $client = new SoapClient('JBidBonds.wsdl');
            return $client;
        } catch (SoapFault $e) {
            // log & raise exception
        }
    }

    protected function processResponse($response, $response_property){
        if($response->ResponseCode == "00"){
            return [
                'status' => 'success',
                'data' => $response->$response_property
            ];
        } else if($response->ResponseCode == "55"){
            if($this->auth_count === 0){
                $this->getSessionID(false);

                $ex = new Exception();
                $trace = $ex->getTrace();
                $caller = $trace[1];

                return call_user_func_array(array($this, $caller['function']), $caller['args']);
            }
        }

        /**
         * return error message if provided by client else provide one ourselves below
         */

        $error_message = "System error";

        switch ($response->ResponseCode) {
            case '05':
                $error_message = "Invalid Details";
                break;
            case '09':
                $error_message = "Request in Process";
                break;
            //list others below
        }

        return [
            'status' => 'error',
            'error_message' => $error_message
        ];
    }


    protected function getUniqueID($prefix = 'JBID-', $length = 15)
    {
        $keyspace = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $str = '';

        $max = mb_strlen($keyspace, '8bit') - 1;

        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }

        return $prefix . $str;
    }


}
