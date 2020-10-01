<?php


namespace Tests\Feature\Http\Controllers;


use App\Agent;
use App\Business;
use App\Jobs\NotifyUser;
use App\Mail\NotifyUserCreated;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AgentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function create_agent_validates_empty_request()
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->json('post', '/api/v1/agent/create', []);
        $response->assertJson(["error" => [
            "The email field is required.",
            "The physical address field is required.",
            "The postal address field is required.",
            "The phone field is required.",
            "The agent type field is required.",
            "The postal code id field is required.",
            "The firstname field is required.",
            "The lastname field is required.",
            "The id number field is required."
        ],
            "code" => 422]);
    }

    /** @test */
    public function create_individual_agent()
    {
        $admin = User::first();
        $user = factory(User::class)->make();
        $agent = factory(Agent::class)->make([
            "phone" => $user->phone_number,
            "email" => $user->email,
            "agent_type" => 'individual',
            "created_by" => $admin->id
        ]);
        $response = $this->actingAs($admin)->json('post', '/api/v1/agent/create', [
            "firstname" => $user->firstname,
            "lastname" => $user->lastname,
            "id_number" => $user->id_number,
            "email" => $user->email,
            "phone" => $user->phone_number,
            "postal_code_id" => 1,
            "agent_type" => $agent->agent_type,
            "physical_address" => $agent->physical_address,
            "postal_address" => $agent->postal_address,
        ]);
        $response->assertJson([
            "email" => $user->email,
            "phone" => $user->phone_number,
            "physical_address" => $agent->physical_address,
            "postal_address" => $agent->postal_address,
            "postal_code_id" => 1,
            "agent_type" => $agent->agent_type,
        ]);
        $this->assertDatabaseHas('users',[
            "firstname" => $user->firstname,
            "lastname" => $user->lastname,
            "id_number" => $user->id_number,
            "email" => $user->email,
            "phone_number" => $user->phone_number,
            "verified_email" => "1",
            "verified_otp" => "1",
            "verified_phone" => "1"
        ]);
        $this->assertDatabaseHas('agents',[
            "name" =>  $user->firstname. ' '. $user->lastname,
            "email" => $user->email,
            "phone" => $user->phone_number,
            "physical_address" => $agent->physical_address,
            "postal_address" => $agent->postal_address,
            "postal_code_id" => 1,
            "agent_type" => $agent->agent_type,
        ]);
    }

    /** @test */
    public function create_business_agent_gets_notification()
    {
        $admin = User::first();
        $user = factory(User::class)->make();
        $crp = random_int(10000,39999);
        $agent = factory(Agent::class)->make([
            "agent_type" => 'business',
            "created_by" => $admin->id,
            "crp" => $crp
        ]);

        Mail::fake();

        $response = $this->actingAs($admin)->json('post', '/api/v1/agent/create', [
            "business_name" => $agent->name,
            "business_email" => $agent->email,
            "business_phone" => $agent->phone,
            "business_physical_address" => $agent->physical_address,
            "business_postal_address" => $agent->postal_address,
            "business_postal_code_id" => 1,
            "crp" => $agent->crp,
            "firstname" => $user->firstname,
            "lastname" => $user->lastname,
            "id_number" => $user->id_number,
            "email" => $user->email,
            "phone" => $user->phone_number,
            "postal_code_id" => 1,
            "agent_type" => $agent->agent_type,
            "physical_address" => $agent->physical_address,
            "postal_address" => $agent->postal_address,
        ]);


        $response->assertJson(["email" => $agent->email,
            "phone" => $agent->phone,
            "physical_address" => $agent->physical_address,
            "postal_address" => $agent->postal_address,
            "postal_code_id" => 1,
            "agent_type" => $agent->agent_type,
        ]);

        $this->assertDatabaseHas('agents', [
            "name" => $agent->name,
            "crp" => $agent->crp,
        ]);

        Mail::assertSent(NotifyUserCreated::class);
    }

}
