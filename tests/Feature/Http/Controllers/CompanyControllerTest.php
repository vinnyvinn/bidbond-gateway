<?php


namespace Tests\Feature\Http\Controllers;


use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function create_company_validates_empty_request()
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->json('post', '/api/v1/companies', []);
        $response->assertJson(["error" => [
            "The crp field is required.",
            "The email field is required.",
            "The name field is required.",
            "The phone number field is required.",
            "The physical address field is required.",
            "The postal address field is required.",
            "The postal code id field is required.",
            "The company resolution field is required."
        ], "code" => 422]);
    }




}