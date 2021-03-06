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

    const INGREDIENTS = [
        0 => ['name' => 'name', 'price' => 11, 'amount' => 1],
        1 => ['name' => 'name', 'price' => 11, 'amount' => 1],
    ];

    const ITEM_NAME = 'itemName';

    protected $user;
    protected $store;
    protected $item;

    protected function setUp()
    {
        parent::setUp();
        $this->store = factory(Store::class)->create();
        $this->user = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::StoreUser,
        ]);
        $this->item = factory(Item::class)->create(['store_id' => $this->store->id]);
    }
    /**
     * 8. POST /api/v1/store/{store}/items
     *
     * @test
     * @throws \Exception
     */
    public function AddItemToStore_DataCorrect_Success()
    {
        $response = $this->json('POST', 'api/v1/store/' . $this->store->id . '/items', [
            'api_token' => $this->user->api_token,
            'name' => self::ITEM_NAME,
            'ingredients' => self::INGREDIENTS,
        ]);

        $response->assertStatus(201);
        $response->assertJson(["success" => true]);

        $itemFromDB = Item::where('name', self::ITEM_NAME)->first();
        $this->assertEquals($itemFromDB->ingredients()->get()->count(), count(self::INGREDIENTS));
    }

    /**
     * 9. PATCH /api/v1/store/{store}/items/{item}
     *
     * @test
     * @throws \Exception
     */
    public function UpdateStoreItem_IngredientsPassed_Success()
    {
        $response = $this->json('PATCH', 'api/v1/store/' . $this->store->id . '/items/' . $this->item->id, [
            'api_token' => $this->user->api_token,
            'name' => self::ITEM_NAME,
            'ingredients' => self::INGREDIENTS,
        ]);

        $response->assertStatus(200);
        $response->assertJson(["success" => true]);

        $itemFromDB = Item::where('store_id', $this->store->id)->first();
        $this->assertEquals($itemFromDB->store_id, $this->store->id);
        $this->assertEquals($itemFromDB->name, self::ITEM_NAME);
        $this->assertEquals($itemFromDB->ingredients()->get()->count(), count(self::INGREDIENTS));
    }

    /**
     * 9. PATCH /api/v1/store/{store}/items/{item}
     *
     * @test
     * @throws \Exception
     */
    public function UpdateStoreItem_NoIngredientsPassed_Success()
    {
        $numberOfIngredients = 3;
        factory(ItemIngredients::class, $numberOfIngredients)->create([
            'ingredient_id' => factory(ItemIngredient::class)->create()->id,
            'item_id' => $this->item->id,
        ]);
        $response = $this->json('PATCH', 'api/v1/store/' . $this->store->id . '/items/' . $this->item->id, [
            'api_token' => $this->user->api_token,
            'name' => self::ITEM_NAME,
        ]);

        $response->assertStatus(200);
        $response->assertJson(["success" => true]);

        $itemFromDB = Item::where('store_id', $this->store->id)->first();
        $this->assertEquals($itemFromDB->store_id, $this->store->id);
        $this->assertEquals($itemFromDB->name, self::ITEM_NAME);
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
        factory(OrderItem::class)->create([
            'order_id' => factory(Order::class)->create([
                'store_id' => $this->store->id,
                'status' => OrderStatus::Placed,
            ])->id,
            'item_id' => $this->item->id,
        ]);
        $response = $this->json('DELETE', 'api/v1/store/' . $this->store->id . '/items/' . $this->item->id, [
            'api_token' => $this->user->api_token,
        ]);

        $response->assertStatus(400);
    }

    /**
     * 10. DELETE /api/v1/store/{store}/items/{item}
     *
     * @test
     * @throws \Exception
     */
    public function DeleteStoreItem_ItemNotInStore_NotFound()
    {
        $item = factory(Item::class)->create([
            'store_id' => factory(Store::class)->create()->id,
        ]);
        factory(OrderItem::class)->create([
            'order_id' => factory(Order::class)->create([
                'store_id' => $this->store->id,
            ])->id,
        ]);
        $response = $this->json('DELETE', 'api/v1/store/' . $this->store->id . '/items/' . $item->id, [
            'api_token' => $this->user->api_token,
        ]);

        $response->assertStatus(404);
    }

    /**
     * 10. DELETE /api/v1/store/{store}/items/{item}
     *
     * @test
     * @throws \Exception
     */
    public function DeleteStoreItem_ItemIsNotUsed_Success()
    {
        factory(OrderItem::class)->create([
            'order_id' => factory(Order::class)->create([
                'store_id' => $this->store->id,
                'status' => OrderStatus::Canceled,
            ])->id,
            'item_id' => $this->item->id,
        ]);
        $response = $this->json('DELETE', 'api/v1/store/' . $this->store->id . '/items/' . $this->item->id, [
            'api_token' => $this->user->api_token,
        ]);

        $this->assertFalse(Item::where('id', $this->item->id)->exists());
        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
    }
}
