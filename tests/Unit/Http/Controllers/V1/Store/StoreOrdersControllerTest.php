<?php

namespace Tests\Unit\Http\Controllers\V1;

use App\Models\Order;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreOrdersControllerTest extends TestCase
{
    use RefreshDatabase;

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
        
        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
        $this->assertEquals(Order::find($order->id)->status, $orderStatusNew);

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
        $orderStatusOld = OrderStatus::Placed;
        $orderStatusNew = OrderStatus::Canceled;

        $user = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => $userRole,
        ]);
        $order = factory(Order::class)->create([
            'status' => $orderStatusOld,
            'store_id' => factory(Store::class)->create()
        ]);

        $response = $this->json('PATCH', 'api/v1/store/' . $store->id . '/order/' . $order->id, [
            'api_token' => $user->api_token,
            'status' => $orderStatusNew,
        ]);

        $response->assertStatus(404);
    }

    /**
     * 12. PATCH /api/v1/store/{store}/order/{order}
     *
     * @test
     * @throws \Exception
     */
    public function UpdateOrder_CustomerTryChangeOtherOrder_Fail()
    {
        $orderStatusNew = OrderStatus::Canceled;
        $store = factory(Store::class)->create();
        $customer = factory(User::class)->create([
            'api_token' => str_random(30),
        ]);

        $order = factory(Order::class)->create([
            'customer_id' => factory(User::class)->create()
        ]);

        $response = $this->json('PATCH', 'api/v1/store/' . $store->id . '/order/' . $order->id, [
            'api_token' => $customer->api_token,
            'status' => $orderStatusNew,
        ]);

        $response->assertStatus(403);
    }

}
