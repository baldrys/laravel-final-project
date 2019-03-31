<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CartItemsTableSeeder::class);
        $this->call(ItemIngredientTableSeeder::class);
        $this->call(ItemIngredientsTableSeeder::class);
        $this->call(StoreUsersTableSeeder::class);
        
    }
}
