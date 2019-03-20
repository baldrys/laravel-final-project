<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\Models\StoreUser;
use App\Models\Store;
use App\Models\User;
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

$factory->define(StoreUser::class, function (Faker $faker) {
    $user = factory(User::class)->create([
        'role'=> UserRole::StoreUser,
    ]);

    return [
        'store_id' => function () {
            return Store::inRandomOrder()->first()->id;
        },
        'user_id' => $user->id,
    ];
});
