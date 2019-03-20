<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\Models\Item;
use App\Models\ItemIngredient;
use App\Models\ItemIngredients;

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

$factory->define(ItemIngredients::class, function (Faker $faker) {
    $minAmountItemIngredients = 1;
    $maxAmountItemIngredients = 15;
    return [
        'item_id' => function () {
            return Item::inRandomOrder()->first()->id;
        },

        'ingredient_id' => function () {
            return ItemIngredient::inRandomOrder()->first()->id;
        },

        'amount' => $faker->numberBetween(
            $min = $minAmountItemIngredients, 
            $max = $maxAmountItemIngredients
            )
    ];
});