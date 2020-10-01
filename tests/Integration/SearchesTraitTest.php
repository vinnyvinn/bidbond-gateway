<?php


namespace Tests\Integration;


use App\Traits\Searches;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BaseUnitTestCase;

class SearchesTraitTest extends BaseUnitTestCase
{
    use RefreshDatabase, Searches;

    /** @test
     * matches @method searchById
     */
    public function informa_searchById_working()
    {
        $response = $this->searchById([
            'id_number' => '28194838',
            'citizenship' => 'kenyan',
            'firstname' => 'Martin',
            'middlename' => 'Iriga',
            'referral_code' => null,
            'lastname' => 'Kagume',
        ]);

        $this->assertEquals([
            "first_name" => "MARTIN",
            "middle_name" => "IRIGA",
            "last_name" => "KAGUME",
            "id_number" => "28194838",
            "gender" => "Male",
            "date_of_birth" => "03/06/1991",
            "citizenship" => "Kenyan",
            "person_search_type" => "ID",
            "kra_pin" => "A005512422H",
            "valid" => 1
        ], $response);
    }

    /** @test */
    public function informa_searchByPhoneNId_working()
    {
        $id_number = "28194838";
        $phone = "0712704404";
        $valid = $this->searchByPhoneNId($phone,$id_number);
        $this->assertTrue($valid);
    }
}