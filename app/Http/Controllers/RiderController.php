<?php

namespace App\Http\Controllers;

use App\Restaurateur;
use App\Rider;
use App\Order;
use Illuminate\Http\Request;
use App\HttpResponseCode;
use Illuminate\Support\Facades\Auth;

class RiderController extends Controller
{
    /**
     * Create new rider
     */
    public function create(Request $request) {
        $validator = Rider::checkCreateRequest($request);
        if (!$validator->fails()) {
            $createdRider = new Rider;

            $createdRider->name = $request->name;
            $createdRider->surname = $request->surname;
            $createdRider->phone_number = $request->phone_number;
            $createdRider->email = $request->email;
            $createdRider->password = $request->password;

            $createdRider->save();

            $message = Rider::find($createdRider->id);
            $code = HttpResponseCode::CREATED;

        }
        else {
            $message = $validator->errors();
            $code = HttpResponseCode::BAD_REQUEST;
        }

        return response()->json($message, $code);
    }

    /**
     * Get details for a given rider
     */
    public function read($id) {
        $rider = Rider::find($id);

        if(isset($rider)) {
            $message = $rider;
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ['message' => 'Rider not found'];
            $code = HttpResponseCode::NOT_FOUND;
        }

        return response()->json($message, $code);
    }

    /**
     * Get details of the current logged in rider
     */
    public function readCurrent() {
        $rider = Auth::guard('rider')->user();

        if(isset($rider)) {
            $message = $rider;
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ['message' => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Update data of the current logged in rider
     */
    public function update(Request $newData) {
        $rider = Auth::guard('rider')->user();
        $id = $rider->id;

        if(isset($rider)){
            $validator = Rider::checkUpdateRequest($newData);
            if(!$validator->fails()){
                $rider->name = $newData->name;
                $rider->surname = $newData->surname;
                $rider->phone_number = $newData->phone_number;
                $rider->email = $newData->email;
                $rider->password = $newData->password;

                $rider->save();

                $message = Rider::find($id);
                $code = HttpResponseCode::OK;
            }
            else{
                $message = $validator->errors();
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else{
            $message = ['message' => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Delete the account of the current logged in rider
     */
    public function delete() {
        $rider = Auth::guard('rider')->user();

        if(isset($rider)){
            $deleted = $rider->delete();
            if($deleted) {
                $message = ['message' => "Deleted"];
                $code = HttpResponseCode::OK;
            }
            else {
                $message = ['message' => "Unable to delete rider"];
                $code = HttpResponseCode::SERVER_ERROR;
            }
        }
        else {
            $message = ['message' => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Log in a rider by email and password
     * In case of success the remember_token must be setted and returned in the resposne
     */
    public function login(Request $request) {
        $rider = Rider::attemptLogin($request->email, $request->password);
        if(isset($rider)) {
            $token = $rider->setApiToken();
            if(isset($request->device_id)) {
                $rider->setDeviceId($request->device_id);
            }
            $header = ['Authorization' => 'Bearer '.$token];

            return response()->json($rider, HttpResponseCode::OK, $header);
        }
        else {
            return response()->json(['message' => 'Wrong email or password'], HttpResponseCode::UNAUTHORIZED);
        }
    }

    /**
     * Log out the current logged in rider
     * In case of success the remeber_token must be removed
     */
    public function logout() {
        $rider = Auth::guard('rider')->user();
        if(isset($rider)) {
            $rider->removeApiToken();
            $rider->removeDeviceId();
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
        $rider = Rider::find($id);
        $result = $rider->sendNotification($request->title, $request->message, $request->click_action, $request->data);
        return response()->json($result);
    }

    public function setCurrentLocation(Request $request) {
        $rider = Auth::guard('rider')->user();
        if(isset($rider)) {
            if(isset($request->latitude) && isset($request->longitude)) {
                $rider->setLocation($request->latitude, $request->longitude);
                $message = ['message' => 'OK'];
                $code = HttpResponseCode::OK;
            }
            else {
                $message = ['message' => 'Coordinates not provided'];
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else {
            $message = ['message' => 'User not recognized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function startService(){
        $rider = Auth::guard('rider')->user();
        if(isset($rider)){
            $rider->operating = true;
            $rider->save();
            $message = ['message' => 'Service started'];
            $code = HttpResponseCode::OK;
        }
        else{
            $message = ['message' => 'Unauthorized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function stopService(){
        $rider = Auth::guard('rider')->user();
        if(isset($rider)){
            $rider->operating = false;
            $rider->longitude = null;
            $rider->latitude = null;
            $rider->save();
            $message = ['message' => 'Service stopped'];
            $code = HttpResponseCode::OK;
        }
        else{
            $message = ['message' => 'Unauthorized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function changePassword(Request $request){
        $rider = Auth::guard('rider')->user();
        if(isset($rider)) {
            if (isset($request->old_password) && isset($request->new_password)) {
                $result = $rider->changePassword($request->old_password, $request->new_password);
                $rider->removeApiToken();
                $rider->removeDeviceId();
                if($result) {
                    $message = ['message' => 'Ok'];
                    $code = HttpResponseCode::OK;
                }
                else {
                    $message = ['message' => 'Error'];
                    $code = HttpResponseCode::BAD_REQUEST;
                }
            }
            else {
                $message = ['message' => 'Data not provided'];
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else {
            $message = ['message' => 'User not recognized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }
}
