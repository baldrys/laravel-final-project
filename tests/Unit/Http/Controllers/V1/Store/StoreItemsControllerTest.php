<?php

namespace Tests\Unit\Http\Controllers\V1;

use App\Models\Item;
use App\Models\ItemIngredient;
use App\Models\ItemIngredients;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreItemsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 8. POST /api/v1/store/{store}/items
     *
     * @test
     * @throws \Exception
     */
    public function AddItemToStore_DataCorrect_Success()
    {
        $itemName = 'ItemName';
        $ingredients = [
            0 => ['name' => 'name', 'price' => 11, 'amount' => 1],
            1 => ['name' => 'name', 'price' => 11, 'amount' => 1],
        ];
        $store = factory(Store::class)->create();
        $user = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::StoreUser,
        ]);

        $response = $this->json('POST', 'api/v1/store/' . $store->id . '/items', [
            'api_token' => $user->api_token,
            'name' => $itemName,
            'ingredients' => $ingredients,
        ]);

        $response->assertStatus(200);
        $response->assertJson(["success" => true]);

        $itemFromDB = Item::where('store_id', $store->id)->first();
        $this->assertEquals($itemFromDB->name, $itemName);
        $this->assertEquals($itemFromDB->ingredients()->get()->count(), count($ingredients));
    }

    /**
     * 9. PATCH /api/v1/store/{store}/items/{item}
     *
     * @test
     * @throws \Exception
     */
    public function UpdateStoreItem_IngredientsPassed_Success()
    {
        $itemName = 'ItemName';
        $ingredients = [
            0 => ['name' => 'name', 'price' => 11, 'amount' => 1],
            1 => ['name' => 'name', 'price' => 11, 'amount' => 1],
        ];
        $store = factory(Store::class)->create();
        $user = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::StoreUser,
        ]);

        $item = factory(Item::class)->create();
        $response = $this->json('PATCH', 'api/v1/store/' . $store->id . '/items/' . $item->id, [
            'api_token' => $user->api_token,
            'name' => $itemName,
            'ingredients' => $ingredients,
        ]);

        $response->assertStatus(200);
        $response->assertJson(["success" => true]);

        $itemFromDB = Item::where('store_id', $store->id)->first();
        $this->assertEquals($itemFromDB->store_id, $store->id);
        $this->assertEquals($itemFromDB->name, $itemName);
        $this->assertEquals($itemFromDB->ingredients()->get()->count(), count($ingredients));
    }

    /**
     * 9. PATCH /api/v1/store/{store}/items/{item}
     *
     * @test
     * @throws \Exception
     */
    public function UpdateStoreItem_NoIngredientsPassed_Success()
    {
        $itemName = 'ItemName';
        $numberOfIngredients = 3;
        $store = factory(Store::class)->create();
        $user = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::StoreUser,
        ]);

        $item = factory(Item::class)->create();
        factory(ItemIngredients::class, $numberOfIngredients)->create([
            'ingredient_id' => factory(ItemIngredient::class)->create()->id,
            'item_id' => $item->id,
        ]);
        $response = $this->json('PATCH', 'api/v1/store/' . $store->id . '/items/' . $item->id, [
            'api_token' => $user->api_token,
            'name' => $itemName,
        ]);

        $response->assertStatus(200);
        $response->assertJson(["success" => true]);

        $itemFromDB = Item::where('store_id', $store->id)->first();
        $this->assertEquals($itemFromDB->store_id, $store->id);
        $this->assertEquals($itemFromDB->name, $itemName);
        $this->assertEquals($itemFromDB->ingredients()->get()->count(), $numberOfIngredients);
    }

    /**
     * 10. DELETE /api/v1/store/{store}/items/{item}
     *
     * @test
     * @throws \Exception
     */
    public function DeleteStoreItem_ItemIsUsed_CantDelete()
    {
        $store = factory(Store::class)->create();
        $user = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::StoreUser,
        ]);
        $item = factory(Item::class)->create(['store_id' => $store->id]);
        factory(OrderItem::class)->create([
            'order_id' => factory(Order::class)->create([
                'store_id' => $store->id,
                'status' => OrderStatus::Placed,
            ])->id,
            'item_id' => $item->id,
        ]);
        $response = $this->json('DELETE', 'api/v1/store/' . $store->id . '/items/' . $item->id, [
            'api_token' => $user->api_token,
        ]);

        $response->assertStatus(400);
        $response->assertJson(["success" => false]);
    }

    /**
     * 10. DELETE /api/v1/store/{store}/items/{item}
     *
     * @test
     * @throws \Exception
     */
    public function DeleteStoreItem_ItemNotInStore_NotFound()
    {
        $store = factory(Store::class)->create();

        $user = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::StoreUser,
        ]);
        $item = factory(Item::class)->create([
            'store_id' => factory(Store::class)->create()->id,
        ]);
        factory(OrderItem::class)->create([
            'order_id' => factory(Order::class)->create([
                'store_id' => $store->id,
                'status' => OrderStatus::Placed,
            ])->id,
        ]);
        $response = $this->json('DELETE', 'api/v1/store/' . $store->id . '/items/' . $item->id, [
            'api_token' => $user->api_token,
        ]);

        $response->assertStatus(404);
        $response->assertJson(["success" => false]);
    }

    /**
     * 10. DELETE /api/v1/store/{store}/items/{item}
     *
     * @test
     * @throws \Exception
     */
    public function DeleteStoreItem_ItemIsNotUsed_Success()
    {
        $store = factory(Store::class)->create();
        $user = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::StoreUser,
        ]);
        $item = factory(Item::class)->create(['store_id' => $store->id]);
        factory(OrderItem::class)->create([
            'order_id' => factory(Order::class)->create([
                'store_id' => $store->id,
                'status' => OrderStatus::Canceled,
            ])->id,
            'item_id' => $item->id,
        ]);
        $response = $this->json('DELETE', 'api/v1/store/' . $store->id . '/items/' . $item->id, [
            'api_token' => $user->api_token,
        ]);

        $this->assertFalse(Item::where('id', $item->id)->exists());
        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
    }
}
