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
     * Проверяет все ли items в корзине из одного store
     *
     * @param  User $user
     *
     * @return boolean
     */
    public static function isAllItemsInSameStore(User $user)
    {
        $numberItemsNotInSameStore = $user->cartItems()
            ->get()
            ->unique("store_id")
            ->count();
        if ($numberItemsNotInSameStore > 1) {
            return false;
        }
        return true;
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
        $cartItem = self::where('user_id', $user->id)->first();
        if ($cartItem && (self::getStoreIdFromUserCart($user) != $item->store_id)) {
            return false;
        }
        return true;
    }

    public static function getStoreIdFromUserCart(User $user)
    {
        return self::where('user_id', $user->id)->first()->item->store_id;
    }

    public static function getCartPriceForUser(User $user)
    {
        $cartItems = CartItem::where('user_id', $user->id)->get();
        $price = 0;
        foreach ($cartItems as $cartItem) {
            $price += $cartItem->amount * $cartItem->item->getPrice();
        }
        return $price;
    }

    public static function clearCartForUser(User $user)
    {
        $cartItems = CartItem::where('user_id', $user->id)->get();
        foreach ($cartItems as $cartItem) {
            $cartItem->delete();
        }
    }
}
