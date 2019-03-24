<?php

use Illuminate\Database\Seeder;
use App\Models\CartItem;
use App\Models\User;
use App\Models\Store;
use App\Models\Item;
// use Faker\Generator as Faker;

class CartItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        $minAmount = 1;
        $maxAmount = 5;
        $numberOfUsersAndStores = 3;
        $numberOfItemsInCart = 3;
        foreach (range(1, $numberOfUsersAndStores) as $i) {
            $customer = factory(User::class)->create();
            $store = factory(Store::class)->create();
            foreach (range(1, $numberOfItemsInCart) as $j) {
                $item = factory(Item::class)->create([
                    'store_id' => $store->id,
                    'name' => 'Item'.$j.' from store'.$i,
                ]);
                factory(CartItem::class)->create([
                    'user_id' =>$customer->id,
                    'item_id' =>$item->id,
                    'amount' =>  $faker->numberBetween($min = $minAmount, $max = $maxAmount)
                ]);
            }
        }
    }
}
