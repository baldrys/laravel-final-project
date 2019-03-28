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

Route::group(['prefix' => 'v1'], function ()
{

    /*
    |-----------------------------------------------------------------------
    | Public routes
    |-----------------------------------------------------------------------
    */

    Route::get('/login', 'V1\Auth\AuthController@login');
    Route::get('/register', 'V1\Auth\AuthController@register');

    Route::group([
        'middleware' => ["auth:api"]
    ], function ()
    {

        /*
        |-----------------------------------------------------------------------
        | Andmin and Customer routes
        |-----------------------------------------------------------------------
        */

        Route::group(['middleware' => ['role_check:'.UserRole::Admin.','.UserRole::Customer]], function() {

            Route::group(['prefix' => 'cart'], function () {
                Route::post('item/{item}', 'V1\Cart\CartController@addItemToCart');
                Route::delete('item/{item}', 'V1\Cart\CartController@deleteItemFromCart');
                Route::post('checkout', 'V1\Cart\CartController@checkout');
            });

            Route::group(['prefix' => 'me'], function () {
                Route::get('info', 'V1\Me\UserController@info');
                Route::get('orders', 'V1\Me\UserController@getOrders');
            });

        });

        /*
        |-----------------------------------------------------------------------
        | Andmin and StoreUser routes
        |-----------------------------------------------------------------------
        */

        Route::group(['middleware' => ['role_check:'.UserRole::Admin.','.UserRole::StoreUser]], function() {

            Route::group(['prefix' => 'store'], function () {
                Route::post('{store}/items', 'V1\Store\StoreItemsController@addItemToStore');
                Route::patch('{store}/items/{item}', 'V1\Store\StoreItemsController@updateStoreItem');
                Route::delete('{store}/items/{item}', 'V1\Store\StoreItemsController@deleteStoreItem');
                Route::get('{store}/orders', 'V1\Store\StoreOrdersController@getStoreOrders');
            });

        });

        /*
        |-----------------------------------------------------------------------
        | Andmin and StoreUser and Customer routes
        |-----------------------------------------------------------------------
        */

        Route::group(['middleware' => ['role_check:'.UserRole::Admin.','.UserRole::Customer.','.UserRole::StoreUser]], function() {

            Route::group(['prefix' => 'store'], function () {
                Route::patch('{store}/order/{order}', 'V1\Store\StoreOrdersController@updateStoreOrder');
            });
        });

        /*
        |-----------------------------------------------------------------------
        | Andmin routes
        |-----------------------------------------------------------------------
        */

        Route::group(['middleware' => ['role_check:'.UserRole::Admin]], function() {

            Route::group(['prefix' => 'store'], function () {
                Route::post('{store}/users', 'V1\Store\StoreUsersController@addStoreUser');
                Route::delete('{store}/users/{user}', 'V1\Store\StoreUsersController@deleteStoreUser');
            });
        });

    });

});