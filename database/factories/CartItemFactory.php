<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\Models\Item;
use App\Models\CartItem;
use App\Models\User;
use App\Models\Store;
use App\Support\Enums\UserRole;

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

$factory->define(CartItem::class, function (Faker $faker) {
    // Куда вынести константы?
    $minAmountInCart = 1;
    $maxAmountInCart = 15;
    return [
        'amount' => $faker->numberBetween($min = $minAmountInCart, $max = $maxAmountInCart),
        'item_id' => function () {
            return Item::inRandomOrder()->first()->id;
        },
        'user_id' =>  function () {
            return User::inRandomOrder()->first()->id;
        },
    ];
});
