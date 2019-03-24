<?php

use Illuminate\Database\Seeder;
use App\Models\ItemIngredient;

class ItemIngredientTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (range(1, 5) as $index) {
            factory(ItemIngredient::class)->create([
                'name' =>'Item ingredient name '.$index,
            ]);
        }
    }
}
