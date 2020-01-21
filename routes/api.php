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

Route::post(CUSTOMER, 'CustomerController@create');
Route::post(CUSTOMER.'/login', 'CustomerController@login');
Route::middleware(['auth:customer'])->group(function () {
    Route::post(CUSTOMER.'/logout', 'CustomerController@logout');
    Route::get(CUSTOMER, 'CustomerController@readCurrent');
    Route::put(CUSTOMER.'/update', 'CustomerController@update');
    Route::delete(CUSTOMER.'/delete', 'CustomerController@delete');
});

Route::post(RESTAURATEUR, 'RestaurateurController@create');
Route::post(RESTAURATEUR.'/login', 'RestaurateurController@login');
Route::get(RESTAURATEUR.'/read/{id}','RestaurateurController@read');
Route::middleware(['auth:restaurateur'])->group(function () {
    Route::post(RESTAURATEUR.'/logout', 'RestaurateurController@logout');
    Route::get(RESTAURATEUR, 'RestaurateurController@readCurrent');
    Route::put(RESTAURATEUR.'/update', 'RestaurateurController@update');
    Route::delete(RESTAURATEUR.'/delete', 'RestaurateurController@delete');
});

Route::post(RIDER, 'RiderController@create');
Route::post(RIDER.'/login', 'RiderController@login');
Route::get(RIDER.'/read/{id}','RiderController@read');
Route::middleware(['auth:rider'])->group(function () {
    Route::post(RIDER.'/logout', 'RiderController@logout');
    Route::get(RIDER, 'RiderController@readCurrent');
    Route::put(RIDER.'/update', 'RiderController@update');
    Route::delete(RIDER.'/delete', 'RiderController@delete');
});
