<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Order;

class OrderItem extends Model
{
    public $timestamps = false;
    protected $table = 'order_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 'item_id', 'amount',
    ];

    /**
     * Сохраняет items из cart_items в order_items
     *
     * @param  Order $order
     * @param  Collection $cartItems
     *
     * @return void
     */
    public static function saveCartItemsOfOrder(Order $order, Collection $cartItems) {
        foreach ($cartItems as $cartItem) {
            self::create([
                'order_id' => $order->id,
                'item_id' => $cartItem->item->id,
                'amount' => $cartItem->amount,
            ]);
        }
    }
}
