<?php

use Illuminate\Database\Seeder;
use App\Models\ItemIngredients;

class ItemIngredientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ItemIngredients::class, 10)->create();
    }
}
