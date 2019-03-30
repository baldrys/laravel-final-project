<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public $timestamps = false;
    protected $table = 'items';
    protected $hidden = ['pivot'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'store_id'
    ];

    public function ingredients()
    {
        return $this->belongsToMany('App\Models\ItemIngredient', 'item_ingredients', 'item_id', 'ingredient_id');
    }

    public function itemIngredients()
    {
        return $this->hasMany('App\Models\ItemIngredients');
    }

    public function orders()
    {
        return $this->belongsToMany('App\Models\Order', 'order_items', 'item_id', 'order_id');
    }

    /**
     * Счиает цену item
     *
     * @return float
     */
    public function getPrice()
    {
        $itemPrice = $this->itemIngredients->sum(function($itemIngredient) {
            $ingredientAmount = $itemIngredient->amount;
            $ingredientPrice = $itemIngredient->itemIngredient->price;
            return $ingredientAmount * $ingredientPrice;
        });
        return $itemPrice;
    }

    /**
     * Сохраняет переданные ингридиенты для item
     *
     * @param  mixed $ingredients
     *
     * @return void
     */
    public function saveIngredients(Array $ingredients) {

        foreach ($ingredients as $ingredient) {

            $newIngredient = ItemIngredient::create([
                'store_id' => $this->store_id,
                'name' => $ingredient['name'],
                'price' => $ingredient['price'],
            ]);

            ItemIngredients::create([
                'item_id' => $this->id,
                'ingredient_id' => $newIngredient->id,
                'amount' => $ingredient['amount'],        
            ]);

        }
    }

    /**
     * Заменяет ингридиенты у item
     *
     * @param  mixed $ingredients
     *
     * @return void
     */
    public function replaceIngredients(Array $ingredients) {
        $this->itemIngredients()->delete();
        $this->saveIngredients($ingredients);
    }

}
