<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\Models\Store;
use App\Models\ItemIngredient;

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

$factory->define(ItemIngredient::class, function (Faker $faker) {
    $minItemIngredientPrice = 1.0;
    $maxItemIngredientPrice = 1000.0;
    return [
        'store_id' => function () {
            return Store::inRandomOrder()->first()->id;
        },
        'price' => $faker->randomFloat(
            $nbMaxDecimals = 2, 
            $min = $minItemIngredientPrice, 
            $max = $maxItemIngredientPrice
        ),
        'name' => Str::random(10),    
    ];
});