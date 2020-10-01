<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'email' => 'jbidsuperadmin@jamiiborabank.co.ke',
            'firstname' => 'super',
            'lastname' => 'admin',
            'middlename' => 'creater',
            'phone_number' => '0700000000',
            'id_number' => '11111111',
            'verified_otp' => 1,
            'verified_phone' => 1,
            'active' => 1,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);
        $user->assign('superadmin');


//        $customers = User::whereIs('customer')->get();

//        $rms = factory(App\User::class,20)->create()->each(function ($rm) use($customers){
//            $rm->assign('relationship_manager');
//            $customer = $customers->random();
//            $customer->referral_code = $rm->user_unique_id;
//            $customer->save();
//        });
//
//        $customers->each(function ($customer) use($rms){
//            $rm = $rms->random();
//            $customer->referral_code = $rm->user_unique_id;
//            $customer->save();
//        });

    }
}
