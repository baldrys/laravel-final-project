<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $timestamps = false;
    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id', 'customer_id', 'status', 'total_price',
    ];


    public function orderItems()
    {
        return $this->hasMany('App\Models\OrderItem');
    }
}
