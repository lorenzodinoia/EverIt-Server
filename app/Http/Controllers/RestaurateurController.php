<?php

namespace App\Http\Controllers;

use App\Restaurateur;
use App\City;
use App\ShopType;
use App\ProductCategory;
use App\Product;
use Illuminate\Http\Request;
use App\HttpResponseCode;
use Illuminate\Support\Facades\Auth;

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
        $restaurateur = Restaurateur::find($id);
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
                $restaurateur->cap = $newData->cap;
                $restaurateur->phone_number = $newData->phone_number;
                $restaurateur->email = $newData->email;
                if(isset($newData->password)) {
                    $restaurateur->password = $newData->password;
                }
                $restaurateur->piva = $newData->piva;
                if(isset($newData->description)){
                    $restaurateur->description = $newData->description;
                }
                $restaurateur->delivery_cost = $newData->delivery_cost;
                if(isset($newData->min_quantity)) {
                    $restaurateur->min_quantity = $newData->min_quantity;
                }
                if(isset($newData->order_range_time)) {
                    $restaurateur->order_range_time = $newData->order_range_time;
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

    public function addProducts(Request $request) {
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)) {
            $success = true;

            foreach ($request->products as $product) {
                if(isset($product['product_category']['id'])) {
                    $category = ProductCategory::find($categoryId);
                }
                else if(isset($product['product_category']['name'])) {
                    $name = $product['product_category']['name'];
                    $category = ProductCategory::where('name', $name)->first();
                    if(!isset($category)) {
                        $category = new ProductCategory;
                        $category->name = $name;
                        $category->restaurateur()->associate($restaurateur);
                        $category->save();
                    }
                }
                
                if(isset($category)) {
                    $newProduct = new Product;
                    $newProduct->name = $product['name'];
                    $newProduct->price = $product['price'];
                    $newProduct->details = $product['details'];
                    $newProduct->restaurateur()->associate($restaurateur);
                    $newProduct->productCategory()->associate($category);
                    $newProduct->save();
                }
                else {
                    $success = false;
                }
            }

            $message = ['message' => $success];
            $code = ($success) ? HttpResponseCode::CREATED : HttpResponseCode::SERVER_ERROR;
        }
        else {
            $message = ['message' => 'User not recognized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function readProductCategories() {
        $restaurateur = Auth::guard('restaurateur')->user();

        if(isset($restaurateur)) {
            return response()->json($restaurateur->productCategories()->get(), HttpResponseCode::OK);
        }
    }
}
