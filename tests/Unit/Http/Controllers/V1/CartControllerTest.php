<?php

namespace Tests\Unit\Http\Controllers\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Store;
use App\Models\Item;
use App\Models\ItemIngredient;
use App\Models\ItemIngredients;
use App\Models\CartItem;
use App\Models\Order;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 3. POST api/v1/cart/item/{item}
     *
     * @test
     * @throws \Exception
     */
    public function AddItemToCart_DataCorrect_Success()
    {
        $amount = 2;
        $store = factory(Store::class)->create();
        $user = factory(User::class)->create(['api_token' => str_random(30)]);
        $item = factory(Item::class)->create([
            'store_id' => $store->id,
            'name' => str_random(10),
        ]);
        $response = $this->json('POST', 'api/v1/cart/item/'.$item->id, [
            'api_token' => $user->api_token,
            'amount' => $amount,
        ]);
        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
    }

    /**
     * 3. POST api/v1/cart/item/{item}
     *
     * @test
     * @throws \Exception
     */
    public function AddItemToCart_ItemFromAnotherStore_Fail()
    {
        $amount = 2;
        $store1 = factory(Store::class)->create();
        $store2 = factory(Store::class)->create();
        $user = factory(User::class)->create(['api_token' => str_random(30)]);
        $item1 = factory(Item::class)->create([
            'store_id' => $store1->id,
            'name' => str_random(10),
        ]);
        $item2 = factory(Item::class)->create([
            'store_id' => $store2->id,
            'name' => str_random(10),
        ]);
        CartItem::create([
            'user_id' => $user->id,
            'item_id' => $item1->id,
            'amount' => $amount,
        ]);
        $response = $this->json('POST', 'api/v1/cart/item/'.$item2->id, [
            'api_token' => $user->api_token,
            'amount' => $amount,
        ]);
        $response->assertStatus(400);
        $response->assertJson(["success" => False]);
    }


    /**
     * 3. DELETE api/v1/cart/item/{item}
     *
     * @test
     * @throws \Exception
     */
    public function DeleteItemFromCart_ItemInCart_Success()
    {
        $amount = 2;
        $store = factory(Store::class)->create();
        $user = factory(User::class)->create(['api_token' => str_random(30)]);
        $item = factory(Item::class)->create([
            'store_id' => $store->id,
            'name' => str_random(10),
        ]);
        CartItem::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'amount' => $amount,
            'store_id' => $item->store_id,
        ]);
        $response = $this->json('DELETE', 'api/v1/cart/item/'.$item->id, [
            'api_token' => $user->api_token,
        ]);
        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
    }

    /**
     * 3. DELETE api/v1/cart/item/{item}
     *
     * @test
     * @throws \Exception
     */
    public function DeleteItemFromCart_ItemNotInCart_NotFound()
    {
        $store = factory(Store::class)->create();
        $user = factory(User::class)->create(['api_token' => str_random(30)]);
        $item = factory(Item::class)->create([
            'store_id' => $store->id,
            'name' => str_random(10),
        ]);
        $response = $this->json('DELETE', 'api/v1/cart/item/'.$item->id, [
            'api_token' => $user->api_token,
        ]);
        $response->assertStatus(404);
        $response->assertJson(["success" => false]);
    }

    /**
     * 4. POST /api/v1/cart/checkout
     *
     * @test
     * @throws \Exception
     */
    public function Chechout_EmptyCart_BadRequest()
    {
        $store = factory(Store::class)->create();
        $user = factory(User::class)->create(['api_token' => str_random(30)]);
        $item = factory(Item::class)->create([
            'store_id' => $store->id,
            'name' => str_random(10),
        ]);
        $response = $this->json('POST', 'api/v1/cart/checkout', [
            'api_token' => $user->api_token,
        ]);
        $response->assertStatus(400);
        $response->assertJson(["success" => false]);
    }

    /**
     * 4. POST /api/v1/cart/checkout
     * 
     * Test case:
     * 
     * item1 = [[ingredient1, amount1], [ingredient2, amount1]]
     * item2 = [[ingredient1, amount1]]
     * 
     * order = [[item1, amountInCartItem1], {item2, amountInCartItem2]]
     * 
     * @test
     * @throws \Exception
     */
    public function Chechout_ItemsInCart_Success()
    {
        $priceOfIngridient1 = 4.20;
        $priceOfIngridient2 = 14.88;
        $amount1 = 3;
        $amount2 = 5;
        $amountInCartItem1 = 2;
        $amountInCartItem2 = 7;

        $store = factory(Store::class)->create();
        $user = factory(User::class)->create(['api_token' => str_random(30)]);

        $itemIngredient1 = factory(ItemIngredient::class)->create([
            'store_id' => $store->id,
            'price' => $priceOfIngridient1
        ]);
        $itemIngredient2 = factory(ItemIngredient::class)->create([
            'store_id' => $store->id,
            'price' => $priceOfIngridient2
        ]);

        $item1 = factory(Item::class)->create(['store_id' => $store->id]);
        $item2 = factory(Item::class)->create(['store_id' => $store->id]);

        factory(ItemIngredients::class)->create([
            'item_id' => $item1->id,
            'ingredient_id' => $itemIngredient1->id,
            'amount' => $amount1,
        ]);

        factory(ItemIngredients::class)->create([
            'item_id' => $item1->id,
            'ingredient_id' => $itemIngredient2->id,
            'amount' => $amount2,
        ]);

        factory(ItemIngredients::class)->create([
            'item_id' => $item2->id,
            'ingredient_id' => $itemIngredient1->id,
            'amount' => $amount1,
        ]);

        factory(CartItem::class)->create([
            'user_id' => $user->id,
            'item_id' => $item1->id,
            'amount' => $amountInCartItem1,
        ]);

        factory(CartItem::class)->create([
            'user_id' => $user->id,
            'item_id' => $item2->id,
            'amount' => $amountInCartItem2,
        ]);

        $response = $this->json('POST', 'api/v1/cart/checkout', [
            'api_token' => $user->api_token,
        ]);

        $totalPrice = Order::where('customer_id', $user->id)
            ->first()
            ->total_price;

        $priceOfItem1 = $amount1*$priceOfIngridient1 + $amount2*$priceOfIngridient2;
        $priceOfItem2 = $amount1*$priceOfIngridient1;
        $actualTotalPrice = $amountInCartItem1*$priceOfItem1 + $amountInCartItem2*$priceOfItem2;

        $this->assertEquals($totalPrice, $actualTotalPrice, '', 0.0001);    
        $response->assertStatus(200);
    }
}
