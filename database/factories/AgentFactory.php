<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Agent;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Agent::class, function (Faker $faker) {
    $agent_type = ['individual','business'];

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'phone' => "254" . random_int(700000000, 799999999),
        'physical_address' => $faker->address,
        'postal_address' => random_int(1,200),
        'postal_code_id' => random_int(1,5),
        'agent_type' => $agent_type[random_int(0,1)],
        'created_by' => 1,
        'crp' =>  NULL
    ];
});
