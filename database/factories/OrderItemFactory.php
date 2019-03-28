<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;

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

$factory->define(OrderItem::class, function (Faker $faker) {
    $minAmountItem = 1;
    $maxAmountItem = 15;
    return [
        'order_id' => function () {
            return Order::inRandomOrder()->first()->id;
        },
        'item_id' => function () {
            return Item::inRandomOrder()->first()->id;
        },
        'amount' => $faker->numberBetween(
            $min = $minAmountItem, 
            $max = $maxAmountItem
            )
    ];
});