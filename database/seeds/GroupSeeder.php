<?php

use App\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Group::firstOrCreate(['name' => 'customer']);
        Group::firstOrCreate(['name' => 'agent']);
    }
}
