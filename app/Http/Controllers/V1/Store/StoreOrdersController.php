<?php

namespace App\Http\Controllers\V1\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\GetOrdersRequest;
use App\Http\Requests\V1\OrderStatusRequest;
use App\Http\Transformers\V1\OrderTransformer;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Support\Enums\OrderStatus;
use Illuminate\Http\Request;

class StoreOrdersController extends Controller
{
    const ORDERS_PER_PAGE = 10;

    /**
     * @param Store $store
     * @param GetOrdersRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStoreOrders(Store $store, GetOrdersRequest $request)
    {
        $orders = Order::where('store_id', $store->id)
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

    /**
     * @param Store $store
     * @param Order $order
     * @param OrderStatusRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStoreOrder(Store $store, Order $order, OrderStatusRequest $request)
    {

        if ($order->store_id != $store->id) {
            abort(404, "Order " . $order->id . " не из store " . $store->id);

        }

        $statuOld = $order->status;
        $statusNew = $request->status;
        $user = auth("api")->user();
        $isAllowed = $user->isAllowedOrderStatusChange($statuOld, $statusNew);

        if (!$isAllowed) {
            abort(403, "Нет доступа для групппы: " . $user->role);
        }

        $order->status = $request->status;
        $order->save();
        return response()->json([
            "success" => true,
            "data" => [
                "updated_order" => OrderTransformer::transformItem($order),
            ],
        ]);
    }
}
