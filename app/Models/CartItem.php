<?php

namespace App\Models;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    public $timestamps = false;
    protected $table = 'cart_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'item_id', 'amount',
    ];

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id');
    }

    /**
     * Вовзращает true если item можно добавить в корзину,
     * т.е. если item из того же магазина, что и другие item в корзине
     *
     * @param  Item $item
     * @param  User $user
     *
     * @return boolean
     */
    public static function isAllowedToAdd(Item $item, User $user)
    {
        $cartItem = $user->cartItems->first();
        if ($cartItem && ($cartItem->item->store_id != $item->store_id)) {
            return false;
        }
        return true;
    }

    /**
     * Считает цену корзины user
     * 
     * @param  User $user
     *
     * @return float
     */
    public static function getCartPriceForUser(User $user)
    {
        $cartPrice = $user->cartItems->sum(function($cartItem) {
            return $cartItem->amount * $cartItem->item->getPrice();
        });
        return $cartPrice;
    }

}
