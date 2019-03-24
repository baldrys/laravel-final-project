<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'full_name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','api_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function cartItems()
    {
        return $this->belongsToMany('App\Models\Item', 'cart_items', 'user_id', 'item_id');
    }

    /**
     * Генерация уникального токена
     *
     * @return void
     */
    public function rollApiKey()
    {
        do {
            $this->api_token = str_random(30);
        } while ($this->where('api_token', $this->api_token)->exists());
        $this->save();
    }
}
