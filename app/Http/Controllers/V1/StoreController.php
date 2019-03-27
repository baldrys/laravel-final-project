<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AddItemToStoreRequest;
use App\Http\Requests\V1\EmailPasswordRequest;
use App\Http\Requests\V1\GetOrdersRequest;
use App\Http\Requests\V1\IngredientsRequest;
use App\Http\Requests\V1\OrderStatusRequest;
use App\Http\Transformers\V1\ItemTransformer;
use App\Http\Transformers\V1\OrderTransformer;
use App\Http\Transformers\V1\UserTransformer;
use App\Models\Item;
use App\Models\Order;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\UserRole;
use Hash;
use Illuminate\Http\Request;

class StoreController extends Controller
{

    const ORDERS_PER_PAGE = 2;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function addItemToStore(Store $store, AddItemToStoreRequest $request)
    {
        $item = Item::create([
            'name' => $request->name,
            'store_id' => $store->id,
        ]);
        $ingredients = $request->ingredients;
        if ($ingredients) {
            $item->saveIngredients($ingredients);
        }
        return response()->json([
            "success" => true,
            "data" => [
                "item" => ItemTransformer::transformItem($item),
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStoreItem(Store $store, Item $item, IngredientsRequest $request)
    {
        $name = $request->name;
        if ($name) {
            $item->name = $name;
        }

        $item->store_id = $store->id;
        $item->save();

        $ingredients = $request->ingredients;
        if ($ingredients) {
            $item->replaceIngredients($ingredients);
        }

        return response()->json([
            "success" => true,
            "data" => [
                "item" => ItemTransformer::transformItem($item),
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteStoreItem(Store $store, Item $item, Request $request)
    {
        if ($item->store_id != $store->id) {
            return response()->json([
                "success" => false,
                "message" => "Item " . $item->id . " нету в store " . $store->id,
            ], 400);
        }

        $orders = $item->orders()->where('status', '<>', OrderStatus::Canceled)->get();

        if ($orders->count() > 0) {
            return response()->json([
                "success" => false,
                "message" => "Невозможно удалить items. Используется в следующих заказах.",
                "orders" => OrderTransformer::transformCollection($orders),
            ], 400);
        }

        $item->delete();
        return response()->json([
            "success" => true,
            "data" => [
                "deleted_item" => ItemTransformer::transformItem($item),
            ],
        ]);

    }

    /**
     * @param Request $request
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStoreOrder(Store $store, Order $order, OrderStatusRequest $request)
    {

        if ($order->store_id != $store->id) {
            return response()->json([
                "success" => false,
                "message" => "Order " . $order->id . " не из store " . $store->id,
            ], 400);
        }

        $statuOld = $order->status;
        $statusNew = $request->status;
        $user = auth("api")->user();
        $isAllowed = $user->isAllowedOrderStatusChange($statuOld, $statusNew);

        if (!$isAllowed) {
            return response()->json([
                "success" => false,
                "message" => "Нет доступа для групппы: " . $user->role,
            ], 403);
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addStoreUser(Store $store, EmailPasswordRequest $request)
    {

        $user = factory(User::class)->create([
            "full_name" => $request->full_name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "role" => UserRole::StoreUser,
        ]);
        StoreUser::create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);
        return response()->json([
            "success" => true,
            "data" => [
                "created_store_user" => UserTransformer::transformItem($user),
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteStoreUser(Store $store, User $user, Request $request)
    {
        if ($user->role != UserRole::StoreUser) {
            return response()->json([
                "success" => false,
                "message" => "User " . $user->id . " не является userStore",
            ], 400);
        }

        $storeUser = StoreUser::where('user_id', $user->id)->where('store_id', $store->id);
        if (!$storeUser->exists()) {
            return response()->json([
                "success" => false,
                "message" => "User " . $user->id . " не является сотрудником store " . $store->id,
            ], 400);
        }

        $user->delete();
        return response()->json([
            "success" => true,
        ]);

    }

}
