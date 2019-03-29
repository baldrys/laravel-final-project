<?php

namespace Tests\Unit\Http\Controllers\V1\Cart;

use App\Models\CartItem;
use App\Models\Item;
use App\Models\ItemIngredient;
use App\Models\ItemIngredients;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
        $item = factory(Item::class)->create();
        $response = $this->json('POST', 'api/v1/cart/item/' . $item->id, [
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
        ]);
        $item2 = factory(Item::class)->create([
            'store_id' => $store2->id,
        ]);
        CartItem::create([
            'user_id' => $user->id,
            'item_id' => $item1->id,
            'amount' => $amount,
        ]);
        $response = $this->json('POST', 'api/v1/cart/item/' . $item2->id, [
            'api_token' => $user->api_token,
            'amount' => $amount,
        ]);
        $response->assertStatus(400);
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
        $item = factory(Item::class)->create();
        CartItem::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'amount' => $amount,
        ]);
        $response = $this->json('DELETE', 'api/v1/cart/item/' . $item->id, [
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
        $item = factory(Item::class)->create();
        $response = $this->json('DELETE', 'api/v1/cart/item/' . $item->id, [
            'api_token' => $user->api_token,
        ]);
        $response->assertStatus(404);
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
        $item = factory(Item::class)->create();
        $response = $this->json('POST', 'api/v1/cart/checkout', [
            'api_token' => $user->api_token,
        ]);
        $response->assertStatus(400);
    }

    /**
     * 4. POST /api/v1/cart/checkout
     *
     * @test
     * @throws \Exception
     */
    public function Chechout_ItemsInCart_Success()
    {
        $priceOfIngridient = 4.20;
        $amountInCartItem = 2;
        $amountOfingredient = 3;
        $numberOfIngredients = 4;

        $store = factory(Store::class)->create();
        $user = factory(User::class)->create(['api_token' => str_random(30)]);
        $item = factory(Item::class)->create();
        factory(ItemIngredients::class, $numberOfIngredients)->create([
            'item_id' => $item->id,
            'ingredient_id' => factory(ItemIngredient::class)->create(['price' => $priceOfIngridient])->id,
            'amount' => $amountOfingredient,
        ]);
        factory(CartItem::class)->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'amount' => $amountInCartItem,
        ]);

        $response = $this->json('POST', 'api/v1/cart/checkout', [
            'api_token' => $user->api_token,
        ]);

        $totalPrice = Order::where('customer_id', $user->id)
            ->first()
            ->total_price;

        $actualTotalPrice = $amountInCartItem * $amountOfingredient * $numberOfIngredients * $priceOfIngridient;
        $this->assertEquals($totalPrice, $actualTotalPrice);
        $response->assertStatus(200);
    }
}
