<?php


namespace Tests\Integration;


use App\Services\BidBondService;
use App\Services\CompanyService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\BaseUnitTestCase;

class ServicesTest extends BaseUnitTestCase
{
    use RefreshDatabase;


    /** @test
     * matches @method searchById
     */
    public function fetch_settings_from_bidbond_service()
    {
        $bidBondService = new BidBondService();
        $response = json_decode($bidBondService->obtainSettings(),true);

        $this->assertTrue(Arr::has($response,'bank_limit'));
        $this->assertTrue(Arr::has($response,'company_limit'));
        $this->assertTrue(Arr::has($response,'indemnity_cost'));
        $this->assertTrue(Arr::has($response,'cr12_search_cost'));
    }

    /** @test */
    public function fetch_approved_companies_from_company_service()
    {
        $companyService = new CompanyService();
        $response = json_decode($companyService->obtainApprovedCompanies([]),true);

        if(sizeof($response["data"])> 0){
            $this->assertEquals($response["data"][0]["approval_status"],"approved");
        }else{
            $this->assertEmpty($response);
        }
    }

    /** @test */
    public function request_payment_from_payment_service()
    {
        $paymentService = new PaymentService();
        $response = json_decode($paymentService->paymentRequest([
            'phone' => '0712704404',
            'amount' => 00,
            'account' => 'TEST'
        ]),true);
        $this->assertEquals($response["errorCode"],"400.002.02");
    }
}
