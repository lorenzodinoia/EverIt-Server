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

Route::post(CUSTOMER, 'CustomerController@create');
Route::post(CUSTOMER.'/login', 'CustomerController@login');
Route::middleware(['auth:customer'])->group(function () {
    Route::post(CUSTOMER.'/logout', 'CustomerController@logout');
    Route::get(CUSTOMER, 'CustomerController@readCurrent');
});

