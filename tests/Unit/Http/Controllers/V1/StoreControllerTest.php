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
use Hash;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
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
        $item = factory(Item::class)->create();
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

    /**
     * 11. GET /api/v1/store/{store}/orders
     *
     * @test
     * @throws \Exception
     */
    public function GetStoreOrders_DataCorrect_Success()
    {
        $min_total_price = 10.00;
        $max_total_price = 20.00;
        $statusForFilter = OrderStatus::Canceled;
        $statusFailFilter = OrderStatus::Placed;
        $numberOfPassedOrders = 4;
        $numberOfNotPassedOrders = 4;

        $store = factory(Store::class)->create();
        $user = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::StoreUser,
        ]);
        factory(Order::class, $numberOfPassedOrders)->create([
            'store_id' => $store->id,
            'status' => $statusForFilter,
            'total_price' => $max_total_price - 1,
        ]);

        factory(Order::class, $numberOfNotPassedOrders)->create([
            'store_id' => $store->id,
            'status' => $statusFailFilter,
            'total_price' => $max_total_price + 1,
        ]);

        $response = $this->json('GET', 'api/v1/store/' . $store->id . '/orders', [
            'api_token' => $user->api_token,
            'status' => $statusForFilter,
            'min_total_price' => $min_total_price,
            'max_total_price' => $max_total_price,
        ]);

        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
        $response->assertJsonCount($numberOfPassedOrders, 'data.orders');
    }

    /**
     * 12. PATCH /api/v1/store/{store}/order/{order}
     *
     * @test
     * @throws \Exception
     */
    public function UpdateOrder_DataCorrect_Success()
    {
        $userRole = UserRole::StoreUser;
        $orderStatusOld = OrderStatus::Placed;
        $orderStatusNew = OrderStatus::Canceled;

        $store = factory(Store::class)->create();
        $user = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => $userRole,
        ]);
        $order = factory(Order::class)->create([
            'status' => $orderStatusOld,
            'store_id' => $store->id,
        ]);

        $response = $this->json('PATCH', 'api/v1/store/' . $store->id . '/order/' . $order->id, [
            'api_token' => $user->api_token,
            'status' => $orderStatusNew,
        ]);

        $this->assertEquals(Order::find($order->id)->status, $orderStatusNew);
        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
    }

    /**
     * 12. PATCH /api/v1/store/{store}/order/{order}
     *
     * @test
     * @throws \Exception
     */
    public function UpdateOrder_NotAllowedStatus_Fail()
    {
        $userRole = UserRole::Customer;
        $orderStatusOld = OrderStatus::Shipped;
        $orderStatusNew = OrderStatus::Canceled;

        $store = factory(Store::class)->create();
        $user = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => $userRole,
        ]);
        $order = factory(Order::class)->create([
            'status' => $orderStatusOld,
            'store_id' => $store->id,
        ]);

        $response = $this->json('PATCH', 'api/v1/store/' . $store->id . '/order/' . $order->id, [
            'api_token' => $user->api_token,
            'status' => $orderStatusNew,
        ]);

        $response->assertStatus(403);
        $response->assertJson(["success" => false]);
    }

    /**
     * 12. PATCH /api/v1/store/{store}/order/{order}
     *
     * @test
     * @throws \Exception
     */
    public function UpdateOrder_OrderNotInStore_NotFound()
    {
        $store = factory(Store::class)->create();
        $userRole = UserRole::Customer;
        $orderStatusOld = OrderStatus::Shipped;
        $orderStatusNew = OrderStatus::Canceled;

        $user = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => $userRole,
        ]);
        $order = factory(Order::class)->create([
            'status' => $orderStatusOld,
        ]);

        $response = $this->json('PATCH', 'api/v1/store/' . $store->id . '/order/' . $order->id, [
            'api_token' => $user->api_token,
            'status' => $orderStatusNew,
        ]);

        $response->assertStatus(404);
        $response->assertJson(["success" => false]);
    }

    /**
     * 13. POST /api/v1/store/{store}/users
     *
     * @test
     * @throws \Exception
     */
    public function AddStoreUser_DataCorrect_Success()
    {
        $name = 'Name';
        $email = 'qwerty@gmail.com';
        $password = 'secret';
        $store = factory(Store::class)->create();
        $admin = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::Admin,
        ]);

        $response = $this->json('POST', 'api/v1/store/' . $store->id . '/users', [
            'api_token' => $admin->api_token,
            "full_name" => $name,
            "email" => $email,
            "password" => $password,
        ]);

        $createdStoreUser = User::where('email', $email)->first();
        $this->assertEquals($createdStoreUser->email, $email);
        $this->assertTrue(Hash::check($password, $createdStoreUser->password));
        $this->assertEquals($createdStoreUser->full_name, $name);
        $this->assertEquals($createdStoreUser->role, UserRole::StoreUser);
        $this->assertEquals(StoreUser::where('user_id', $createdStoreUser->id)->first()->store_id, $store->id);
        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
    }

    /**
     * 14. DELETE /api/v1/store/{store}/users/{user}
     *
     * @test
     * @throws \Exception
     */
    public function DeleteStoreUser_DataCorrect_Success()
    {
        $store = factory(Store::class)->create();
        $admin = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::Admin,
        ]);

        $user = factory(User::class)->create([
            "role" => UserRole::StoreUser,
        ]);

        StoreUser::create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);
        $response = $this->json('DELETE', 'api/v1/store/' . $store->id . '/users/' . $user->id, [
            'api_token' => $admin->api_token,
        ]);

        $this->assertFalse(User::where('id', $user->id)->exists());
        $this->assertFalse(StoreUser::where('store_id', $store->id)->where('user_id', $user->id)->exists());
        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
    }

    /**
     * 14. DELETE /api/v1/store/{store}/users/{user}
     *
     * @test
     * @throws \Exception
     */
    public function DeleteStoreUser_NotUserStore_Fail()
    {
        $store = factory(Store::class)->create();
        $admin = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::Admin,
        ]);

        $user = factory(User::class)->create();

        StoreUser::create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);
        $response = $this->json('DELETE', 'api/v1/store/' . $store->id . '/users/' . $user->id, [
            'api_token' => $admin->api_token,
        ]);

        $response->assertStatus(400);
        $response->assertJson(["success" => false]);
    }

    /**
     * 14. DELETE /api/v1/store/{store}/users/{user}
     *
     * @test
     * @throws \Exception
     */
    public function DeleteStoreUser_NotUserForStore_NotFound()
    {
        $store = factory(Store::class)->create();
        $admin = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::Admin,
        ]);

        $user = factory(User::class)->create([
            "role" => UserRole::StoreUser,
        ]);

        $response = $this->json('DELETE', 'api/v1/store/' . $store->id . '/users/' . $user->id, [
            'api_token' => $admin->api_token,
        ]);

        $response->assertStatus(404);
        $response->assertJson(["success" => false]);
    }
}
