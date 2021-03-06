<?php

namespace App\Http\Controllers;

use App\Notification;
use App\OpeningTime;
use App\Proposal;
use App\Restaurateur;
use App\City;
use App\ShopType;
use App\Rider;
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
            $cretedRestaurateur->latitude = $request->latitude;
            $cretedRestaurateur->longitude = $request->longitude;
            $cretedRestaurateur->phone_number = $request->phone_number;
            $cretedRestaurateur->email = $request->email;
            $cretedRestaurateur->password = $request->password;
            $cretedRestaurateur->vat_number = $request->vat_number;
            $cretedRestaurateur->max_delivery_time_slot = $request->max_delivery_time_slot;
            if(isset($request->delivery_cost)) {
                $cretedRestaurateur->delivery_cost = $request->delivery_cost;
            }
            if(isset($request->min_price)) {
                $cretedRestaurateur->min_price = $request->min_price;
            }
            $shopType = ShopType::find($request->shop_type_id);
            if(isset($shopType)) {
                $cretedRestaurateur->shopType()->associate($shopType);
            }
            else {
                return response()->json(['message' => 'Unable to attach shop type'], HttpResponseCode::BAD_REQUEST);
            }

            $cretedRestaurateur->save();

            $this->saveOpeningTimes($request->input('opening_times'), $cretedRestaurateur);


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
            $message = ["message" => "Can't find commercial activity"];
            $code = HttpResponseCode::NOT_FOUND;
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
            $message = ["message" => "Unauthorized"];
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
                $restaurateur->vat_number = $newData->vat_number;
                $restaurateur->delivery_cost = $newData->delivery_cost;
                if(isset($newData->min_price)) {
                    $restaurateur->min_price = $newData->min_price;
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
                $code = HttpResponseCode::NOT_FOUND;
            }
        }
        else{
            $message = ["message" => "Unauthorized"];
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
                $message = ["message" => "Deleted"];
                $code = HttpResponseCode::OK;
            }
            else{
                $message = ["message" => "Can't delete customer"];
                $code = HttpResponseCode::SERVER_ERROR;
            }
        }
        else{
            $message = ["message" => "Unauthorized"];
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
        $result = $customer->sendNotification($request->title, $request->message, $request->click_action, $request->data);
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

    public function searchRider($idOrder, Request $request){
        $radius = 3;
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)){
            $riders = Rider::havingRaw("DISTANCE(?, ?, last_latitude, last_longitude) <= ?", [$restaurateur->latitude, $restaurateur->longitude, $radius])->get();
            if(isset($riders[0])){
                foreach($riders as $rider){
                    $proposal = new Proposal();
                    $proposal->pickup_time = $request->pickup_time;
                    $proposal->riders()->associate($rider->id);
                    $proposal->order()->associate($idOrder);
                    $proposal->restaurateur()->associate($restaurateur->id);
                    $proposal->save();
                    $notificationFormat = "Hai ricevuto una proposta di consegna da %s per le ore %s";
                    $notificationMessage = sprintf($notificationFormat, $restaurateur->shop_name, $proposal->pickup_time);
                    $rider->sendNotification('Proposta di lavoro', $notificationMessage, Notification::ACTION_RIDER_SHOW_PROPOSAL_DETAIL, ['item_id' => $proposal->id]);

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

    private function saveOpeningTimes($openingDays, $restaurateur){
        foreach ($openingDays as $day) {
            foreach ($day['opening_times'] as $time) {
                $openingTimeSave = new OpeningTime;
                $openingTimeSave->opening_time = $time['opening_time'];
                $openingTimeSave->closing_time = $time['closing_time'];
                $openingTimeSave->opening_day_id = $day['id'];
                $restaurateur->openingTimes()->save($openingTimeSave);
            }
        }
    }

    public function getCurrentRestaurateur(){
        $restaurateurGuard = Auth::guard('restaurateur')->user();
        $restaurateur = Restaurateur::with(['openingTimes'])->find($restaurateurGuard->id);
        if(isset($restaurateur)){
            $message = $restaurateur;
            $code = HttpResponseCode::OK;
        }
        else{
            $message = ["message" => "User not found"];
            $code = HttpResponseCode::NOT_FOUND;
        }
        return response()->json($message, $code);
    }

    public function setNewShopName(Request $request){
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)){
            $validator = Restaurateur::checkUpdateShopName($request);
            if(!$validator->fails()){
                $restaurateur->shop_name = $request->shop_name;
                $restaurateur->save();
                $message = $restaurateur;
                $code = HttpResponseCode::OK;
            }
            else{
                $message = $validator->errors();
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else{
            $message = ["message" => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function setNewEmail(Request $request){
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)){
            $validator = Restaurateur::checkUpdateEmail($request);
            if(!$validator->fails()){
                $restaurateur->email = $request->email;
                $restaurateur->save();
                $message = $restaurateur;
                $code = HttpResponseCode::OK;
            }
            else{
                $message = $validator->errors();
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else{
            $message = ["message" => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function changePassword(Request $request){
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)){
            if(isset($request->old_password) && isset($request->new_password)){
                $result = $restaurateur->changePassword($request->old_password, $request->new_password);
                if($result) {
                    $restaurateur->removeApiToken();
                    $restaurateur->removeDeviceId();
                    $message = ['message' => 'Ok'];
                    $code = HttpResponseCode::OK;
                }
                else {
                    $message = ['message' => "The password doesn't match"];
                    $code = HttpResponseCode::BAD_REQUEST;
                }
            }
            else{
                $message = ['message' => 'Data not provided'];
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else{
            $message = ['message' => 'Unauthorized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

}
