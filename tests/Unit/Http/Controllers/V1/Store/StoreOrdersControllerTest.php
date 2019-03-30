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

    const ORDER_STATUS_OLD = OrderStatus::Placed;
    const ORDER_STATUS_NEW = OrderStatus::Canceled;

    const ORDER_STATUS_NEW_NOT_ALLOWED = OrderStatus::Shipped;
    const USER_ROLE = UserRole::StoreUser;

    protected $user;
    protected $store;
    protected $item;

    protected function setUp()
    {
        parent::setUp();
        $this->store = factory(Store::class)->create();
        $this->user = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => self::USER_ROLE,
        ]);

        $this->order = factory(Order::class)->create([
            'status' => self::ORDER_STATUS_OLD,
            'store_id' => $this->store->id,
        ]);
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

        $storeNew = factory(Store::class)->create();
        factory(Order::class, $numberOfPassedOrders)->create([
            'store_id' => $storeNew->id,
            'status' => $statusForFilter,
            'total_price' => $max_total_price - 1,
        ]);

        factory(Order::class, $numberOfNotPassedOrders)->create([
            'store_id' => $storeNew->id,
            'status' => $statusFailFilter,
            'total_price' => $max_total_price + 1,
        ]);

        $response = $this->json('GET', 'api/v1/store/' . $storeNew->id . '/orders', [
            'api_token' => $this->user->api_token,
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
        $response = $this->json('PATCH', 'api/v1/store/' . $this->store->id . '/order/' . $this->order->id, [
            'api_token' => $this->user->api_token,
            'status' => self::ORDER_STATUS_NEW,
        ]);

        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
        $this->assertEquals(Order::find($this->order->id)->status, self::ORDER_STATUS_NEW);

    }

    /**
     * 12. PATCH /api/v1/store/{store}/order/{order}
     *
     * @test
     * @throws \Exception
     */
    public function UpdateOrder_NotAllowedStatus_Fail()
    {
        $response = $this->json('PATCH', 'api/v1/store/' . $this->store->id . '/order/' . $this->order->id, [
            'api_token' => $this->user->api_token,
            'status' => self::ORDER_STATUS_NEW_NOT_ALLOWED,
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
        $order = factory(Order::class)->create([
            'status' => self::ORDER_STATUS_OLD,
            'store_id' => factory(Store::class)->create(),
        ]);

        $response = $this->json('PATCH', 'api/v1/store/' . $this->store->id . '/order/' . $order->id, [
            'api_token' => $this->user->api_token,
            'status' => self::ORDER_STATUS_NEW,
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
        $customer = factory(User::class)->create([
            'api_token' => str_random(30),
        ]);

        $order = factory(Order::class)->create([
            'customer_id' => factory(User::class)->create(),
        ]);

        $response = $this->json('PATCH', 'api/v1/store/' . $this->store->id . '/order/' . $order->id, [
            'api_token' => $customer->api_token,
            'status' => self::ORDER_STATUS_NEW,
        ]);

        $response->assertStatus(403);
    }

}
