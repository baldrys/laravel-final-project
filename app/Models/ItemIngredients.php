<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemIngredients extends Model
{
    public $timestamps = false;
    protected $table = 'item_ingredients';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'amount', 'item_id', 'ingredient_id'
    ];

    public function itemIngredient()
    {
        return $this->belongsTo('App\Models\ItemIngredient', 'ingredient_id');
    }
}
