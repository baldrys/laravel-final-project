<?php

namespace Tests\Unit\Http\Controllers\V1\Cart;

use App\Models\CartItem;
use App\Models\Item;
use App\Models\ItemIngredient;
use App\Models\ItemIngredients;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    const ITEM_AMOUNT = 2;

    protected $user;
    protected $store;
    protected $item;

    protected function setUp()
    {
        parent::setUp();
        $this->store = factory(Store::class)->create();
        $this->user = factory(User::class)->create(['api_token' => str_random(30)]);
        $this->item = factory(Item::class)->create(['store_id' => $this->store->id]);
    }

    /**
     * 3. POST api/v1/cart/item/{item}
     *
     * @test
     * @throws \Exception
     */
    public function AddItemToCart_DataCorrect_Success()
    {
        $response = $this->json('POST', 'api/v1/cart/items/' . $this->item->id, [
            'api_token' => $this->user->api_token,
            'amount' => self::ITEM_AMOUNT,
        ]);
        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
        $cartItem = CartItem::where('user_id', $this->user->id)->where('item_id', $this->item->id);
        $this->assertTrue($cartItem->exists());
        $this->assertEquals($cartItem->first()->amount, self::ITEM_AMOUNT);
    }

    /**
     * 3. POST api/v1/cart/item/{item}
     *
     * @test
     * @throws \Exception
     */
    public function AddItemToCart_ItemFromAnotherStore_Fail()
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'amount' => self::ITEM_AMOUNT,
        ]);
        $itemNew = factory(Item::class)->create(['store_id' => factory(Store::class)->create()->id]);
        $response = $this->json('POST', 'api/v1/cart/items/' . $itemNew->id, [
            'api_token' => $this->user->api_token,
            'amount' => self::ITEM_AMOUNT,
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
        CartItem::create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'amount' => self::ITEM_AMOUNT,
        ]);
        $response = $this->json('DELETE', 'api/v1/cart/items/' . $this->item->id, [
            'api_token' => $this->user->api_token,
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
        $response = $this->json('DELETE', 'api/v1/cart/items/' . $this->item->id, [
            'api_token' => $this->user->api_token,
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
        $response = $this->json('POST', 'api/v1/cart/checkout', [
            'api_token' => $this->user->api_token,
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
        $numberOfitems = 1;

        factory(ItemIngredients::class, $numberOfIngredients)->create([
            'item_id' => $this->item->id,
            'ingredient_id' => factory(ItemIngredient::class)->create(['price' => $priceOfIngridient])->id,
            'amount' => $amountOfingredient,
        ]);
        factory(CartItem::class)->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'amount' => $amountInCartItem,
        ]);

        $response = $this->json('POST', 'api/v1/cart/checkout', [
            'api_token' => $this->user->api_token,
        ]);

        $totalPrice = Order::where('customer_id', $this->user->id)
            ->first()
            ->total_price;
        $this->assertEquals($this->user->cartItems->count(), 0);
        $this->assertEquals(OrderItem::all()->count(), $numberOfitems);
        $actualTotalPrice = $amountInCartItem * $amountOfingredient * $numberOfIngredients * $priceOfIngridient;
        $this->assertEquals($totalPrice, $actualTotalPrice);
        $response->assertStatus(200);
    }
}
