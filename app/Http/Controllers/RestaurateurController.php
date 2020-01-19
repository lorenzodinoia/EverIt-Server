<?php

namespace App\Http\Controllers;

use App\Restaurateur;
use App\City;
use App\ShopType;
use Illuminate\Http\Request;
use App\HttpResponseCode;
use Illuminate\Support\Facades\Auth;

class RestaurateurController extends Controller
{
    /**
     * Create new restaurateur
     */
    public function create(Request $request) {
        if (Restaurateur::checkCreateRequest($request)) {
            $cretedRestaurateur = new Restaurateur;

            $cretedRestaurateur->shop_name = $request->shop_name;
            $cretedRestaurateur->address = $request->address;
            $cretedRestaurateur->cap = $request->cap;
            $cretedRestaurateur->phone_number = $request->phone_number;
            $cretedRestaurateur->email = $request->email;
            $cretedRestaurateur->password = $request->password;
            $cretedRestaurateur->piva = $request->piva;
            if(isset($request->description)) {
                $cretedRestaurateur->description = $request->description;
            }
            if(isset($request->delivery_cost)) {
                $cretedRestaurateur->delivery_cost = $request->delivery_cost;
            }
            if(isset($request->min_quantity)) {
                $cretedRestaurateur->min_quantity = $request->min_quantity;
            }
            if(isset($request->min_quantity)) {
                $cretedRestaurateur->min_quantity = $request->min_quantity;
            }
            if(isset($request->order_range_time)) {
                $cretedRestaurateur->order_range_time = $request->order_range_time;
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
        }
        else {
            return response()->json(null, HttpResponseCode::BAD_REQUEST);
        }
    }

    /**
     * Get details for a given restaurateur
     */
    public function read($id) {

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

    }

    /**
     * Delete the account of the current logged in restaurateur
     */
    public function delete() {

    }

    /**
     * Log in a restaurateur by email and password
     * In case of success the remember_token must be setted and returned in the resposne
     */
    public function login(Request $request) {
        $restaurateur = Restaurateur::attemptLogin($request->email, $request->password);
        if(isset($restaurateur)) {
            $token = $restaurateur->setApiToken();
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
            $message = ['message' => 'Logout'];
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ['message' => 'User not recognized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }
}
