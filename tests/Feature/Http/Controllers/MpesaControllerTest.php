<?php


namespace Tests\Feature\Http\Controllers;


use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MpesaControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function get_company_search_cost_details()
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->json('get', 'api/v1/company-registration-details', []);
        $response->assertJsonStructure(["amount","account"]);
    }

}