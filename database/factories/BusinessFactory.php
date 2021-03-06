<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Business;
use Faker\Generator as Faker;

$factory->define(Business::class, function (Faker $faker) {
    return [
        "name" => $faker->company,
        "crp" => $faker->word,
        "agent_id" => random_int(2,10)
    ];
});
