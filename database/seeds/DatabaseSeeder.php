<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(BouncerSeeder::class);
        $this->call(KycSeeder::class);
        $this->call(GroupSeeder::class);
        $this->call(BidbondPriceSeeder::class);
        $this->call(PriceSettingSeeder::class);
    }
}
