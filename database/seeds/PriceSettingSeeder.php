<?php

use Illuminate\Database\Seeder;
use App\PriceSetting;

class PriceSettingSeeder extends Seeder
{

    public function run()
    {
        PriceSetting::firstorCreate(['option' => PriceSetting::indemnity_cost], ['value' => 1]);

        PriceSetting::firstorCreate(['option' => PriceSetting::cr12_search_cost], ['value' => 1]);

        PriceSetting::firstorCreate(['option' => PriceSetting::excise_duty], ['value' => 0.2]);
    }
}
