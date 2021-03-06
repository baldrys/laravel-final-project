<?php

use Illuminate\Database\Seeder;
use App\Models\StoreUser;

class StoreUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(StoreUser::class, 3)->create();
    }
}
