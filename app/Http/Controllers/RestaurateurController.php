<?php

namespace App\Http\Controllers;

use App\Proposal;
use App\Restaurateur;
use App\City;
use App\ShopType;
use App\ProductCategory;
use App\Product;
use App\Rider;
use Illuminate\Http\Request;
use App\HttpResponseCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RestaurateurController extends Controller
{
    /**
     * Create new restaurateur
     */
    public function create(Request $request) {
        $validator = Restaurateur::checkCreateRequest($request);
        if (!$validator->fails()) {
            $cretedRestaurateur = new Restaurateur;

            $cretedRestaurateur->shop_name = $request->shop_name;
            $cretedRestaurateur->address = $request->address;
            $cretedRestaurateur->latitude = $request->latitude;
            $cretedRestaurateur->longitude = $request->longitude;
            $cretedRestaurateur->phone_number = $request->phone_number;
            $cretedRestaurateur->email = $request->email;
            $cretedRestaurateur->password = $request->password;
            $cretedRestaurateur->vat_number = $request->vat_number;
            if(isset($request->description)) {
                $cretedRestaurateur->description = $request->description;
            }
            if(isset($request->delivery_cost)) {
                $cretedRestaurateur->delivery_cost = $request->delivery_cost;
            }
            if(isset($request->min_price)) {
                $cretedRestaurateur->min_price = $request->min_price;
            }
            $city = City::find($request->city_id);
            if(isset($city)) {
                $cretedRestaurateur->city()->associate($city);
            }
            else {
                return response()->json(['message' => 'Unable to attach city'], HttpResponseCode::BAD_REQUEST);
            }
            $shopType = ShopType::find($request->shop_type_id);
            if(isset($shopType)) {
                $cretedRestaurateur->shopType()->associate($shopType);
            }
            else {
                return response()->json(['message' => 'Unable to attach shop type'], HttpResponseCode::BAD_REQUEST);
            }

            $cretedRestaurateur->save();
            return response()->json(Restaurateur::find($cretedRestaurateur->id), HttpResponseCode::OK);
        }
        else {
            return response()->json($validator->errors(), HttpResponseCode::BAD_REQUEST);
        }
    }

    /**
     * Get details for a given restaurateur
     */
    public function read($id) {
        $restaurateur = Restaurateur::with(['openingTimes', 'productCategories'])->find($id);
        if(isset($restaurateur)){
            $message = $restaurateur;
            $code = HttpResponseCode::OK;
        }
        else{
            $message = "Can't find commercial activity";
            $code = HttpResponseCode::BAD_REQUEST;
        }

        return response()->json($message, $code);
    }

    /**
     * Get details of the current logged in restaurateur
     */
    public function readCurrent() {
        $restaurateur = Auth::guard('restaurateur')->user();

        if(isset($restaurateur)) {
            $message = $restaurateur;
            $code = HttpResponseCode::OK;
        }
        else {
            $message = "Unauthorized";
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Update data of the current logged in restaurateur
     */
    public function update(Request $newData) {
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)) {
            $id = $restaurateur->id;
            $validator = Restaurateur::checkUpdateRequest($newData);
            if(!$validator->fails()){
                $restaurateur->shop_name = $newData->shop_name;
                $restaurateur->address = $newData->address;
                $restaurateur->phone_number = $newData->phone_number;
                $restaurateur->email = $newData->email;
                if(isset($newData->password)) {
                    $restaurateur->password = $newData->password;
                }
                $restaurateur->vat_number = $newData->vat_number;
                if(isset($newData->description)){
                    $restaurateur->description = $newData->description;
                }
                $restaurateur->delivery_cost = $newData->delivery_cost;
                if(isset($newData->min_price)) {
                    $restaurateur->min_price = $newData->min_price;
                }
                $city = City::find($newData->city_id);
                if(isset($city)) {
                    $restaurateur->city()->associate($city);
                }
                else {
                    return response()->json(['message' => 'Unable to attach city'], HttpResponseCode::BAD_REQUEST);
                }
                $shopType = ShopType::find($newData->shop_type_id);
                if(isset($shopType)) {
                    $restaurateur->shopType()->associate($shopType);
                }
                else {
                    return response()->json(['message' => 'Unable to attach shop type'], HttpResponseCode::BAD_REQUEST);
                }

                $restaurateur->save();
                $message = Restaurateur::find($id);
                $code = HttpResponseCode::OK;
            }
            else{
                $message = $validator->errors();
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else{
            $message = "Unauthorized";
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Delete the account of the current logged in restaurateur
     */
    public function delete() {
        $restaurateur = Auth::guard('restaurateur')->user();

        if(isset($restaurateur)){
            $deleted = $restaurateur->delete();
            if($deleted){
                $message = "Deleted";
                $code = HttpResponseCode::OK;
            }
            else{
                $message = "Can't delete customer";
                $code = HttpResponseCode::SERVER_ERROR;
            }
        }
        else{
            $message = "Unauthorized";
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Log in a restaurateur by email and password
     * In case of success the remember_token must be setted and returned in the resposne
     */
    public function login(Request $request) {
        $restaurateur = Restaurateur::attemptLogin($request->email, $request->password);
        if(isset($restaurateur)) {
            $token = $restaurateur->setApiToken();
            if(isset($request->device_id)) {
                $restaurateur->setDeviceId($request->device_id);
            }
            $header = ['Authorization' => 'Bearer '.$token];

            return response()->json($restaurateur, HttpResponseCode::OK, $header);
        }
        else {
            return response()->json(['message' => 'Wrong email or password'], HttpResponseCode::UNAUTHORIZED);
        }
    }

    /**
     * Log out the current logged in restaurateur
     * In case of success the remeber_token must be removed
     */
    public function logout() {
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)) {
            $restaurateur->removeApiToken();
            $restaurateur->removeDeviceId();
            $message = ['message' => 'Logout'];
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ['message' => 'User not recognized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Test method to send notification
     */
    public function testNotification(Request $request, $id) {
        $customer = Restaurateur::find($id);
        $result = $customer->sendNotification($request->title, $request->message);
        return response()->json($result);
    }

    public function readProductCategories() {
        $restaurateur = Auth::guard('restaurateur')->user();

        if(isset($restaurateur)) {
            return response()->json($restaurateur->productCategories()->get(), HttpResponseCode::OK);
        }
    }

    public function searchNearby($latitude, $longitude) {
        $radius = 3; //Radius in km
        if(isset($latitude) && isset($longitude)) {
            $message = Restaurateur::whereRaw("DISTANCE(?, ?, latitude, longitude) <= ?", [$latitude, $longitude, $radius])->get();
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ['message' => "Coordinates not provided"];
            $code = HttpResponseCode::BAD_REQUEST;
        }

        return response()->json($message, $code);
    }

    public function searchRider($idOrder){
        $radius = 3;
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)){
            $riders = Rider::havingRaw("DISTANCE(?, ?, last_latitude, last_longitude) <= ?", [$restaurateur->latitude, $restaurateur->longitude, $radius])->get();
            if(isset($riders[0])){
                foreach($riders as $rider){
                    $proposal = new Proposal();
                    $proposal->riders()->associate($rider->id);
                    $proposal->order()->associate($idOrder);
                    $proposal->restaurateur()->associate($restaurateur->id);
                    $proposal->save();
                    $notificationFormat = "Hai ricevuto una proposta di consegna da %s";
                    $notificationMessage = sprintf($notificationFormat, $restaurateur->shop_name);
                    $rider->sendNotification('Proposta consegna', $notificationMessage);

                    $message = ['message' => 'Ok'];
                    $code = HttpResponseCode::OK;
                }
            }
            else{
                $message = ['message' => "No rider found"];
                $code = HttpResponseCode::NOT_FOUND;
            }
        }
        else{
            $message = ['message' => 'Unatuhorized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }
}
