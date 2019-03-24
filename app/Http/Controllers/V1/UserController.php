<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Transformers\V1\OrderTransformer;
use App\Http\Transformers\V1\UserTransformer;
use App\Http\Requests\V1\GetOrdersRequest;
use App\Support\Enums\OrderStatus;

class UserController extends Controller
{
    const ORDERS_PER_PAGE = 2;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request)
    {
        $user = auth("api")->user();
        return response()->json([
            "success" => true,
            "data" => [
                "user" => UserTransformer::transformItem($user),
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrders(GetOrdersRequest $request)
    {
        $user = auth("api")->user();
        $orders = Order::where('customer_id', $user->id)
            ->where('status', $request->status)
            ->where('total_price', '>=', $request->min_total_price)
            ->where('total_price', '<=', $request->max_total_price)
            ->paginate(self::ORDERS_PER_PAGE)->except(['data']);
        return response()->json([
            "success" => true,
            "data" => [
                "orders" => OrderTransformer::transformCollection($orders),
            ],
        ]);
    }
}
