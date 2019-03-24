<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemIngredients extends Model
{
    public $timestamps = false;
    protected $table = 'item_ingredients';

    public function itemIngredient()
    {
        return $this->belongsTo('App\Models\ItemIngredient', 'ingredient_id');
    }
}
