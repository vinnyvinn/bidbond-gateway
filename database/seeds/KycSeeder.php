<?php

use App\KycStatus;
use App\Role;
use Illuminate\Database\Seeder;

class KycSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $agent = Role::where('name' , 'agent')->first();
        $kyc = new KycStatus(['status' => 0]);
        $agent->kyc_status()->save($kyc);
    }
}
