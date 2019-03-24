<?php

namespace Tests\Unit\Http\Controllers\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Store;
use App\Models\Item;
use App\Models\CartItem;

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
}
