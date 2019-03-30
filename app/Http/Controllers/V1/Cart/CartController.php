<?php

namespace App\Http\Controllers\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AmountRequest;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Transformers\V1\OrderTransformer;

class CartController extends Controller
{

    /**
     * @param  Item $item
     * @param  AmountRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItemToCart(Item $item, AmountRequest $request)
    {
        $user = auth("api")->user();

        if (!CartItem::isAllowedToAdd($item, $user)) {
            abort(400, "Item из другого магазина!");
        }
        CartItem::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'amount' => $request->amount,
        ]);
        return response()->json([
            "success" => true,
        ]);
    }

    /**
     * @param  Item $item
     * @param  Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteItemFromCart(Item $item, Request $request)
    {
        $user = auth("api")->user();
        $cartItem = CartItem::where('user_id', $user->id)
            ->where('item_id', $item->id);
        if (!$cartItem->exists()) {
            abort(404, "Item'а нету в корзине!");
        }
        $cartItem->delete();
        return response()->json([
            "success" => true,
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout(Request $request)
    {
        $user = auth("api")->user();
        $userCartItems = $user->cartItems;
        if ($userCartItems->isEmpty()) {
            abort(400, "Корзина пуста!");
        }

        $order = Order::create([
            'customer_id' => $user->id,
            'total_price' => CartItem::getCartPriceForUser($user),
            'store_id' => $userCartItems->first()->item->store_id,
        ]);

        OrderItem::saveCartItemsOfOrder($order, $userCartItems);
        $user->cartItems()->delete();
        
        return response()->json([
            "success" => true,
            "data" => [
                "order" => OrderTransformer::transformItem($order),
            ],
        ]);

        return response()->json([
            "success" => true,
            "data" => [
                "order" => OrderTransformer::transformItem($order),
            ],
        ]);
    }
}
