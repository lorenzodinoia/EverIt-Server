<?php

namespace App\Http\Controllers;

use App\Rider;
use Illuminate\Http\Request;
use App\HttpResponseCode;
use Illuminate\Support\Facades\Auth;

class RiderController extends Controller
{
    /**
     * Create new rider
     */
    public function create(Request $request) {
        if (Rider::checkCreateRequest($request)) {
            $createdRider = new Rider;

            $createdRider->name = $request->name;
            $createdRider->surname = $request->surname;
            $createdRider->phone_number = $request->phone_number;
            $createdRider->email = $request->email;
            $createdRider->password = $request->password;

            $createdRider->save();

            return response()->json(Rider::find($createdRider->id), HttpResponseCode::CREATED);
        }
        else {
            return response()->json(null, HttpResponseCode::BAD_REQUEST);
        }
    }

    /**
     * Get details for a given rider
     */
    public function read($id) {

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
            $message = "Unauthorized";
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Update data of the current logged in rider
     */
    public function update(Request $newData) {

    }

    /**
     * Delete the account of the current logged in rider
     */
    public function delete() {

    }

    /**
     * Log in a rider by email and password
     * In case of success the remember_token must be setted and returned in the resposne
     */
    public function login(Request $request) {
        $rider = Rider::attemptLogin($request->email, $request->password);
        if(isset($rider)) {
            $token = $rider->setApiToken();
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
