<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ItemIngredients;

class ItemIngredient extends Model
{
    public $timestamps = false;
    protected $table = 'item_ingredient';
    protected $hidden = ['pivot'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id', 'name', 'price'
    ];
}
