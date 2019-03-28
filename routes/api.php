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

    Route::get('/login', 'V1\AuthController@login');
    Route::get('/register', 'V1\AuthController@register');

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
                Route::post('item/{item}', 'V1\CartController@addItemToCart');
                Route::delete('item/{item}', 'V1\CartController@deleteItemFromCart');
                Route::post('checkout', 'V1\CartController@checkout');
            });

            Route::group(['prefix' => 'me'], function () {
                Route::get('info', 'V1\UserController@info');
                Route::get('orders', 'V1\UserController@getOrders');
            });

        });

        /*
        |-----------------------------------------------------------------------
        | Andmin and StoreUser routes
        |-----------------------------------------------------------------------
        */

        Route::group(['middleware' => ['role_check:'.UserRole::Admin.','.UserRole::StoreUser]], function() {

            Route::group(['prefix' => 'store'], function () {
                Route::post('{store}/items', 'V1\StoreController@addItemToStore');
                Route::patch('{store}/items/{item}', 'V1\StoreController@updateStoreItem');
                Route::delete('{store}/items/{item}', 'V1\StoreController@deleteStoreItem');
                Route::get('{store}/orders', 'V1\StoreController@getStoreOrders');
            });

        });

        /*
        |-----------------------------------------------------------------------
        | Andmin and StoreUser and Customer routes
        |-----------------------------------------------------------------------
        */

        Route::group(['middleware' => ['role_check:'.UserRole::Admin.','.UserRole::Customer.','.UserRole::StoreUser]], function() {

            Route::group(['prefix' => 'store'], function () {
                Route::patch('{store}/order/{order}', 'V1\StoreController@updateStoreOrder');
            });
        });

        /*
        |-----------------------------------------------------------------------
        | Andmin routes
        |-----------------------------------------------------------------------
        */

        Route::group(['middleware' => ['role_check:'.UserRole::Admin]], function() {

            Route::group(['prefix' => 'store'], function () {
                Route::post('{store}/users', 'V1\StoreController@addStoreUser');
                Route::delete('{store}/users/{user}', 'V1\StoreController@deleteStoreUser');
            });
        });

    });

});