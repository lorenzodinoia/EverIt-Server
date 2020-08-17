<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\HttpResponseCode;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Create new customer
     */
    public function create(Request $request) {
        $validator = Customer::checkCreateRequest($request);
        if (!$validator->fails()) {
            $createdCustomer = new Customer;

            $createdCustomer->name = $request->name;
            $createdCustomer->surname = $request->surname;
            $createdCustomer->phone_number = $request->phone_number;
            $createdCustomer->email = $request->email;
            $createdCustomer->password = $request->password;
            $createdCustomer->save();
            $message = Customer::find($createdCustomer->id);
            $code = HttpResponseCode::CREATED;

        }
        else {
            $message = $validator->errors();
            $code = HttpResponseCode::BAD_REQUEST;
        }

        return response()->json($message, $code);
    }

    /**
     * Get details of the current logged in customer
     */
    public function readCurrent($id) {
        $customer = Auth::guard('customer')->user();

        if(isset($customer)) {
            $message = $customer;
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ["message" => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Update data of the current logged in customer
     */
    public function update(Request $newData) {
        $customer = Auth::guard('customer')->user();
        $id =  $customer->id;

        if(isset($customer)){
            $validator = Customer::checkUpdateRequest($newData);
            if(!$validator->fails()){
                $customer->name = $newData->name;
                $customer->surname = $newData->surname;
                $customer->phone_number = $newData->phone_number;
                $customer->email = $newData->email;
                $customer->password = $newData->password;

                $customer->save();

                $message = Customer::find($id);
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

    /**
     * Delete the account of the current logged in customer
     */
    public function delete() {
        $customer = Auth::guard('customer')->user();

        if(isset($customer)){
            $deleted = $customer->delete();
            if($deleted){
                $message = ['message' => 'Deleted'];
                $code = HttpResponseCode::OK;
            }
            else{
                $message = ['message' => "Can't delete customer"];
                $code = HttpResponseCode::SERVER_ERROR;
            }
        }
        else{
            $message = ['message' => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);

    }

    /**
     * Log in a customer by email and password
     * In case of success the remember_token must be setted and returned in the resposne
     */
    public function login(Request $request) {
        $customer = Customer::attemptLogin($request->email, $request->password);
        if(isset($customer)) {
            $token = $customer->setApiToken();
            if(isset($request->device_id)) {
                $customer->setDeviceId($request->device_id);
            }
            $header = ['Authorization' => 'Bearer '.$token];

            return response()->json($customer, HttpResponseCode::OK, $header);
        }
        else {
            return response()->json(['message' => 'Wrong email or password'], HttpResponseCode::UNAUTHORIZED);
        }
    }

    /**
     * Log out the current logged in customer
     * In case of success the remeber_token must be removed
     */
    public function logout() {
        $customer = Auth::guard('customer')->user();
        if(isset($customer)) {
            $customer->removeApiToken();
            $customer->removeDeviceId();
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
        $customer = Customer::find($id);
        $result = $customer->sendNotification($request->title, $request->message, $request->click_action, $request->data);
        return response()->json($result);
    }

    public function changePassword(Request $request) {
        $customer = Auth::guard('customer')->user();
        if(isset($customer)) {
            if (isset($request->old_password) && isset($request->new_password)) {
                $result = $customer->changePassword($request->old_password, $request->new_password);
                $customer->removeApiToken();
                $customer->removeDeviceId();
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
