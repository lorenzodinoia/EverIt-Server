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
const REVIEW = "/review";
const PROPOSAL = "/proposal";

Route::get('echo', function () {
    return 'Hello World';
});

/*
 * Customer endpoints
 */

Route::get("test", function () {
   return "Hello World";
});

Route::post(CUSTOMER, 'CustomerController@create');
Route::post(CUSTOMER.'/login', 'CustomerController@login');
Route::post(CUSTOMER.'/{id}/testNotification', 'CustomerController@testNotification');
Route::middleware(['auth:customer'])->group(function () {
    Route::post(CUSTOMER.'/logout', 'CustomerController@logout');
    Route::get(CUSTOMER.'/{id}', 'CustomerController@readCurrent');
    Route::put(CUSTOMER, 'CustomerController@update');
    Route::delete(CUSTOMER, 'CustomerController@delete');
    Route::post(CUSTOMER.'/changePassword', 'CustomerController@changePassword');

    Route::post(RESTAURATEUR.'/{id}'.ORDER, 'OrderController@create');
    Route::get(CUSTOMER.ORDER.'/getAll', 'OrderController@readCustomerOrders');
    Route::get(CUSTOMER.ORDER.'/{id}', 'OrderController@readAsCustomer');

    Route::post(RESTAURATEUR.'/{idRestaurateur}'.REVIEW, 'ReviewController@create');
    Route::get(CUSTOMER.REVIEW.'/all', 'ReviewController@readCustomerReviews');
    Route::put(CUSTOMER.REVIEW.'/{id}', 'ReviewController@update');
    Route::delete(CUSTOMER.REVIEW.'/{id}', 'ReviewController@delete');
    /*
    Route::put(RESTAURATEUR.FEEDBACK.'/{id}', 'ReviewController@update');
    Route::delete(RESTAURATEUR.FEEDBACK.'/{id}', 'ReviewController@delete');
    Route::get(RESTAURATEUR.FEEDBACK.'/getAll', 'ReviewController@showAllCustomerFeedback');
    */
});

/*
 * Restaurateur endpoints
 */

Route::post(RESTAURATEUR, 'RestaurateurController@create');
Route::post(RESTAURATEUR.'/login', 'RestaurateurController@login');
Route::get(RESTAURATEUR.'/{id}','RestaurateurController@read');
Route::post(RESTAURATEUR.'/{id}/testNotification', 'RestaurateurController@testNotification');

Route::get(RESTAURATEUR.'/{id}'.PRODUCT_CATEGORY, 'ProductCategoryController@readAll');
Route::get(RESTAURATEUR.'/{id}'.PRODUCT_CATEGORY.PRODUCT, 'ProductController@readAllByRestaurateur');
Route::get(RESTAURATEUR.PRODUCT_CATEGORY.'/{categoryId}'.PRODUCT, 'ProductController@readAllByCategory');

Route::post(RESTAURATEUR.'/{id}'.ORDER, 'OrderController@create');
Route::get(RESTAURATEUR.'/{id}'.ORDER.'/availableTimes', 'OrderController@getAvailableDeliveryTime');

Route::get(RESTAURATEUR.'/searchNearby/{latitude}/{longitude}', 'RestaurateurController@searchNearby');

Route::get(RESTAURATEUR.'/{id}'.REVIEW.'/all', 'ReviewController@readRestaurateurReviewsById');

Route::get(RESTAURATEUR.'/{idRestaurateur}'.OPENING_TIMES, 'OpeningTimeController@readAllByRestaurateur');

Route::middleware(['auth:restaurateur'])->group(function () {
    Route::post(RESTAURATEUR.'/logout', 'RestaurateurController@logout');
    Route::get(RESTAURATEUR, 'RestaurateurController@readCurrent');
    Route::put(RESTAURATEUR.'/update', 'RestaurateurController@update');
    Route::delete(RESTAURATEUR.'/delete', 'RestaurateurController@delete');
    Route::post(RESTAURATEUR.'/addProducts', 'RestaurateurController@addProducts');
    Route::get(RESTAURATEUR.'/current/productCategories', 'RestaurateurController@readProductCategories');
    Route::post(RESTAURATEUR.'/image', 'ImageController@saveImageRestaurateur');
    Route::delete(RESTAURATEUR.'/image', 'ImageController@deleteImageRestaurateur');


    Route::get(RESTAURATEUR.ORDER.'/delivered', 'OrderController@readRestaurateurDeliveredOrders');
    Route::get(RESTAURATEUR.ORDER.'/pending', 'OrderController@readRestaurateurPendingOrders');
    Route::get(RESTAURATEUR.ORDER.'/{id}', 'OrderController@readAsRestaurateur');
    Route::post(RESTAURATEUR.ORDER.'/{idOrder}/searchRider', 'RestaurateurController@searchRider');

    Route::post(RESTAURATEUR.PRODUCT_CATEGORY, 'ProductCategoryController@create');
    Route::put(RESTAURATEUR.PRODUCT_CATEGORY.'/{id}', 'ProductCategoryController@update');
    Route::delete(RESTAURATEUR.PRODUCT_CATEGORY.'/{id}', 'ProductCategoryController@delete');

    Route::post(RESTAURATEUR.PRODUCT_CATEGORY.'/{id}'.PRODUCT, 'ProductController@create');
    Route::put(RESTAURATEUR.PRODUCT_CATEGORY.PRODUCT.'/{id}', 'ProductController@update');
    Route::delete(RESTAURATEUR.PRODUCT_CATEGORY.PRODUCT.'/{id}', 'ProductController@delete');

    Route::post(RESTAURATEUR.OPENING_TIMES, 'OpeningTimeController@create');
    Route::post(RESTAURATEUR.OPENING_TIMES.'/addAll', 'OpeningTimeController@createAll');
    Route::delete(RESTAURATEUR.OPENING_TIMES.'/{id}', 'OpeningTimeController@delete');

    Route::get(RESTAURATEUR.REVIEW.'/all', 'ReviewController@readRestaurateurReviews');
});

/*
 * Rider endpoints
 */

Route::post(RIDER, 'RiderController@create');
Route::post(RIDER.'/login', 'RiderController@login');
Route::get(RIDER.'/{id}','RiderController@read');
Route::post(RIDER.'/{id}/testNotification', 'RiderController@testNotification');
Route::middleware(['auth:rider'])->group(function () {
    Route::post(RIDER.'/start', 'RiderController@startService');
    Route::post(RIDER.'/stop', 'RiderController@stopService');
    Route::post(RIDER.'/logout', 'RiderController@logout');
    Route::get(RIDER, 'RiderController@readCurrent');
    Route::put(RIDER.'/update', 'RiderController@update');
    Route::delete(RIDER.'/delete', 'RiderController@delete');

    Route::post(RIDER.'/location', 'RiderController@setCurrentLocation');

    Route::get(RIDER.ORDER.'/assigned', 'OrderController@readRiderAssignedOrders');
    Route::get(RIDER.ORDER.'/{id}', 'OrderController@readAsRider');
    Route::post(RIDER.ORDER.'/{id}/confirmLocation', 'OrderController@confirmRiderInRestaurant');

    Route::post(RIDER.PROPOSAL.'/{id}/accept', 'ProposalController@acceptProposal');
    Route::post(RIDER.PROPOSAL.'/{id}/refuse', 'ProposalController@refuseProposal');
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

