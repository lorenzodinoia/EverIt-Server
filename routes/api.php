<?php

use Illuminate\Http\Request;

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

const CUSTOMER = '/customer';
const RESTAURATEUR = '/restaurateur';
const RIDER = '/rider';
const ORDER = '/order';
const CITY = '/city';
const PRODUCT_CATEGORY = '/productCategory';
const SHOP_TYPE = '/shopType';
const PRODUCT = "/product";
const OPENING_TIMES = "/openingTimes";

/*
 * Customer endpoints
 */

Route::post(CUSTOMER, 'CustomerController@create');
Route::post(CUSTOMER.'/login', 'CustomerController@login');
Route::post(CUSTOMER.'/{id}/testNotification', 'CustomerController@testNotification');
Route::middleware(['auth:customer'])->group(function () {
    Route::post(CUSTOMER.'/logout', 'CustomerController@logout');
    Route::get(CUSTOMER, 'CustomerController@readCurrent');
    Route::put(CUSTOMER.'/update', 'CustomerController@update');
    Route::delete(CUSTOMER.'/delete', 'CustomerController@delete');

    Route::get(CUSTOMER.ORDER, 'OrderController@readCustomerOrders');
    Route::get(CUSTOMER.ORDER.'/{id}', 'OrderController@readAsCustomer');
});

/*
 * Restaurateur endpoints
 */

Route::post(RESTAURATEUR, 'RestaurateurController@create');
Route::post(RESTAURATEUR.'/login', 'RestaurateurController@login');
Route::get(RESTAURATEUR.'/{id}','RestaurateurController@read');
Route::post(RESTAURATEUR.'/{id}/testNotification', 'RestaurateurController@testNotification');

Route::get(RESTAURATEUR.'/{id}'.PRODUCT_CATEGORY, 'ProductCategoryController@readAll');
Route::get(RESTAURATEUR.'/{id}'.PRODUCT, 'ProductController@readAllByRestaurateur');

Route::post(RESTAURATEUR.'/{id}'.ORDER, 'OrderController@create');
Route::get(RESTAURATEUR.'/{id}'.ORDER.'/availableTimes', 'OrderController@getAvailableDeliveryTime');

Route::get(RESTAURATEUR.'/search/nearby', 'RestaurateurController@searchNearby');
Route::middleware(['auth:restaurateur'])->group(function () {
    Route::post(RESTAURATEUR.'/logout', 'RestaurateurController@logout');
    Route::get(RESTAURATEUR, 'RestaurateurController@readCurrent');
    Route::put(RESTAURATEUR.'/update', 'RestaurateurController@update');
    Route::delete(RESTAURATEUR.'/delete', 'RestaurateurController@delete');
    Route::post(RESTAURATEUR.'/addProducts', 'RestaurateurController@addProducts');
    Route::get(RESTAURATEUR.'/current/productCategories', 'RestaurateurController@readProductCategories');

    Route::get(RESTAURATEUR.ORDER.'/delivered', 'OrderController@readRestaurateurDeliveredOrders');
    Route::get(RESTAURATEUR.ORDER.'/pending', 'OrderController@readRestaurateurPendingOrders');
    Route::get(RESTAURATEUR.ORDER.'/{id}', 'OrderController@readAsRestaurateur');

    Route::post(RESTAURATEUR.PRODUCT_CATEGORY, 'ProductCategoryController@create');

    Route::post(RESTAURATEUR.PRODUCT_CATEGORY.'/{id}'.PRODUCT, 'ProductController@create');

    Route::post(RESTAURATEUR.OPENING_TIMES, 'OpeningTimeController@create');
    Route::delete(RESTAURATEUR.OPENING_TIMES.'/{id}', 'OpeningTimeController@delete');
});

/*
 * Rider endpoints
 */

Route::post(RIDER, 'RiderController@create');
Route::post(RIDER.'/login', 'RiderController@login');
Route::get(RIDER.'/{id}','RiderController@read');
Route::post(RIDER.'/{id}/testNotification', 'RiderController@testNotification');
Route::middleware(['auth:rider'])->group(function () {
    Route::post(RIDER.'/logout', 'RiderController@logout');
    Route::get(RIDER, 'RiderController@readCurrent');
    Route::put(RIDER.'/update', 'RiderController@update');
    Route::delete(RIDER.'/delete', 'RiderController@delete');

    Route::post(RIDER.'/location', 'RiderController@setCurrentLocation');

    Route::get(RIDER.ORDER.'/assigned', 'OrderController@readRiderAssignedOrders');
    Route::get(RIDER.ORDER.'/{id}', 'OrderController@readAsRider');
});

/*
 * Others
 */

Route::get(CITY.'/{id}', 'CityController@read');
Route::get(CITY, 'CityController@readAll');

Route::get(PRODUCT_CATEGORY.'/{id}', 'ProductCategoryController@read');
Route::get(PRODUCT_CATEGORY, 'ProductCategoryController@readAll');

Route::get(SHOP_TYPE.'/{id}', 'ShopTypeController@read');
Route::get(SHOP_TYPE, 'ShopTypeController@readAll');

