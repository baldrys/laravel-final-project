<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AmountRequest;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * @param EmailPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItemToCart(Item $item, AmountRequest $request)
    {
        $user = auth("api")->user();

        if(!CartItem::isAllItemsInSameStore($user)){
            return response()->json([
                "success" => false,
                "message" => "В корзине items из разных магазинов!"
            ], 500);
        }
        if (!CartItem::isAllowedToAdd($item, $user)) {
            return response()->json([
                "success" => false,
                "message" => "Item из другого магазина!"
            ], 400);
        }
        CartItem::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'amount' => $request->amount,
            // 'store_id' => $item->store_id,
        ]);
        return response()->json([
            "success" => true,
        ]);
    }

    /**
     * @param EmailPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteItemFromCart(Item $item, Request $request)
    {
        $user = auth("api")->user();
        $cartItem = CartItem::where('user_id', $user->id)
            ->where('item_id', $item->id);
        if (!$cartItem->exists()) {
            return response()->json([
                "success" => false,
                "message" => "Item'а нету в корзине!"
            ], 404);
        }
        $cartItem->delete();
        return response()->json([
            "success" => true,
        ]);
    }

    /**
     * @param EmailPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout(Request $request)
    {
        $user = auth("api")->user();
        $userCartItems = CartItem::where('user_id', $user->id)->get();
        if($userCartItems->isEmpty()) {
            return response()->json([
                "success" => false,
                "message" => "Корзина пуста!"
            ], 400);
        }

        $order = Order::create([
            'customer_id' => $user->id,
            'total_price' => CartItem::getCartPriceForUser($user),
            'store_id' => CartItem::getStoreIdFromUserCart($user),
        ]);

        OrderItem::saveCartItemsOfOrder($order, $userCartItems);
        CartItem::clearCartForUser($user);

        return response()->json([
            "success" => true,
            "data" => [
                "order" => $order,
            ],
        ]);
    }
}