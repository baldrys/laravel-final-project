<?php

namespace App\Http\Controllers\V1\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\GetOrdersRequest;
use App\Http\Transformers\V1\OrderTransformer;
use App\Http\Transformers\V1\UserTransformer;
use App\Models\Order;
use Illuminate\Http\Request;

class UserController extends Controller
{
    const ORDERS_PER_PAGE = 10;

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
     * @param GetOrdersRequest $request
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
