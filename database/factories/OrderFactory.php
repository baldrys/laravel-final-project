<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\Models\User;
use App\Models\Store;
use App\Models\Order;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Order::class, function (Faker $faker) {
    $minTotalPrice = 1.0;
    $maxTotalPrice = 1000.0;
    return [
        'store_id' => function () {
            return Store::inRandomOrder()->first()->id;
        },
        'customer_id' => function () {
            return User::inRandomOrder()->first()->id;
        },
        'total_price'  => $faker->randomFloat(
            $nbMaxDecimals = 2, 
            $min = $minTotalPrice, 
            $max = $maxTotalPrice
        ),
    ];
});