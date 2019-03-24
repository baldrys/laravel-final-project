<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public $timestamps = false;
    protected $table = 'items';
    protected $hidden = ['pivot'];
    
    public function ingredients()
    {
        return $this->belongsToMany('App\Models\ItemIngredient', 'item_ingredients', 'item_id', 'ingredient_id');
    }

    public function itemIngredients()
    {
        return $this->hasMany('App\Models\ItemIngredients');
    }

    public function getPrice()
    {
        $itemIngredients = $this->itemIngredients()->get();
        $sum = 0;
        foreach ($itemIngredients as $itemIngredient) {
            $ingredientAmount = $itemIngredient->amount;
            $ingredientPrice = $itemIngredient->itemIngredient->price;
            $sum += $ingredientAmount * $ingredientPrice;
        }
        return $sum;
    }

}
