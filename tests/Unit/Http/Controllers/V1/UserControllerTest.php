<?php

namespace Tests\Unit\Http\Controllers\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Transformers\V1\UserTransformer;
use App\Models\User;
use App\Support\Enums\OrderStatus;
use App\Models\Order;

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


    /**
     * 7. GET /api/v1/me/orders
     *
     * @test
     * @throws \Exception
     */
    public function GetOrders_DataCorrect_Success()
    {
        $min_total_price = 10.00;
        $max_total_price = 20.00;
        $statusForFilter = OrderStatus::Canceled;
        $statusFailFilter = OrderStatus::Placed;
        $numberOfPassedOrders = 3;
        $numberOfNotPassedOrders = 4;

        $user = factory(User::class)->create([
            'api_token' => str_random(30), 
        ]);

        factory(Order::class, $numberOfPassedOrders)->create([
            'customer_id' => $user->id,
            'status' => $statusForFilter,
            'total_price' => $max_total_price - 1,
        ]);

        factory(Order::class, $numberOfNotPassedOrders)->create([
            'customer_id' => $user->id,
            'status' => $statusFailFilter,
            'total_price' => $max_total_price + 1,
        ]);

        $response = $this->json('GET', 'api/v1/me/orders', [
            'api_token' => $user->api_token,
            'status' => $statusForFilter,
            'min_total_price'=> $min_total_price,
            'max_total_price'=> $max_total_price,
        ]);

        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
        $response->assertJsonCount($numberOfPassedOrders, 'data.orders');
    }
}
