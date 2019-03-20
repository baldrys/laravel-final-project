<?php

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (range(1, 7) as $index) {
            factory(Item::class)->create([
                'name' =>'item name '.$index,
            ]);
        }
    }
}
