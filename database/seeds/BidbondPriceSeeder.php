<?php

use App\BidbondPrice;
use Illuminate\Database\Seeder;

class BidbondPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BidbondPrice::firstOrCreate([
            'lower_bound' => 0,
            'upper_bound' => 2000,
            'cost' => 2000,
            'charge_type' => BidbondPrice::TYPES['fixed'],
            'discount' => 0,
            'discount_type' => BidbondPrice::TYPES['fixed'],
            'group_id' => 1,
            'duration' => 12
        ]);
        BidbondPrice::firstOrCreate([
            'lower_bound' => 0,
            'upper_bound' => 2000,
            'cost' => 2000,
            'charge_type' => BidbondPrice::TYPES['fixed'],
            'discount' => 0,
            'discount_type' => BidbondPrice::TYPES['fixed'],
            'group_id' => 2,
            'duration' => 12
        ]);
        BidbondPrice::firstOrCreate([
            'lower_bound' => 2001,
            'upper_bound' => 10000000,
            'cost' => 1,
            'charge_type' => BidbondPrice::TYPES['percent'],
            'discount' => 0,
            'discount_type' => BidbondPrice::TYPES['fixed'],
            'group_id' => 1,
            'duration' => 12
        ]);
        BidbondPrice::firstOrCreate([
            'lower_bound' => 2001,
            'upper_bound' => 10000000,
            'cost' => 1,
            'charge_type' => BidbondPrice::TYPES['percent'],
            'discount' => 0,
            'discount_type' => BidbondPrice::TYPES['fixed'],
            'group_id' => 2,
            'duration' => 12
        ]);
    }
}
