<?php

namespace Tests\Unit\Http\Controllers\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Transformers\V1\UserTransformer;
use App\Models\User;

class UserControllerTest extends TestCase
{
    /**
     * 6. GET /api/v1/me/info
     *
     * @test
     * @throws \Exception
     */
    public function GetInfo_DataCorrect_Success()
    {
        $user = factory(User::class)->create(['api_token' => str_random(30)]);

        $response = $this->json('GET', 'api/v1/me/info', [
            'api_token' => $user->api_token,
        ]);
        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
        $response->assertSee(json_encode(["user" => UserTransformer::transformItem($user)]));       
    }


    // /**
    //  * 6. GET /api/v1/me/orders
    //  *
    //  * @test
    //  * @throws \Exception
    //  */
    // public function GetOrders_DataCorrect_Success()
    // {
    //     $user = factory(User::class)->create(['api_token' => str_random(30)]);
    //     $store = factory(Store::class)->create();
    //     $items = factory(Item::class, 2)->create([
    //         'store_id' => $store->id,
    //     ]);
    //     $amount = 42;
    //     $totalPrice = 420.69;
    //     $status = 'Placed';
    //     $min_total_price = 1;
    //     $max_total_price = 421.00;

    //     $order = Order::create([
    //         'store_id' => $store->id,
    //         'customer_id' => $user->id,
    //         'total_price' => $totalPrice,
    //     ]);
    //     foreach($items as $item) {
    //         OrderItems::create([
    //             'order_id' => $order->id,
    //             'item_id' => $item->id,
    //             'amount' => $amount, 
    //         ]);
    //     }
    //     $response = $this->json('GET', 'api/v1/me/orders', [
    //         'api_token' => $user->api_token,
    //         'status' => $status,
    //         'min_total_price'=> $min_total_price,
    //         'max_total_price'=> $max_total_price,
    //     ]);

    //     $response->assertStatus(200);
    //     $response->assertJson(["success" => true]);
    //     $response->assertSee(json_encode(["orders" => OrderTransformer::transformCollection($orders)])); 
    // }
}
