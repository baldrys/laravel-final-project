<?php

use Illuminate\Http\Request;
use App\Support\Enums\UserRole;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'v1'], function ()
{
    Route::get('/login', 'V1\AuthController@login');
    Route::get('/register', 'V1\AuthController@register');

    Route::group([
        'middleware' => ["auth:api"]
    ], function ()
    {
        Route::group(['middleware' => ['role_check:'.UserRole::Admin.','.UserRole::Customer]], function() {
            Route::group(['prefix' => 'cart'], function () {
                Route::post('item/{item}', 'V1\CartController@addItemToCart');
                Route::delete('item/{item}', 'V1\CartController@deleteItemFromCart');
                Route::post('checkout', 'V1\CartController@checkout');
            });

            Route::group(['prefix' => 'me'], function () {
                Route::get('info', 'V1\UserController@info');
                Route::get('orders', 'V1\UserController@getOrders');
            });
        });
    });

});