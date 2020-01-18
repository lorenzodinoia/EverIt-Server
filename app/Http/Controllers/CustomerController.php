<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;
use App\HttpResponseCode;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Create new customer
     */
    public function create(Request $request) {
        if (Customer::checkCreateRequest($request)) {
            $createdCustomer = new Customer;

            $createdCustomer->name = $request->name;
            $createdCustomer->surname = $request->surname;
            $createdCustomer->phone_number = $request->phone_number;
            $createdCustomer->email = $request->email;
            $createdCustomer->password = $request->password;

            $createdCustomer->save();

            return response()->json(Customer::find($createdCustomer->id), HttpResponseCode::CREATED);
        }
        else {
            return response()->json(null, HttpResponseCode::BAD_REQUEST);
        }
    }

    /**
     * Get details of the current logged in customer
     */
    public function readCurrent() {
        return response()->json(Auth::guard('customer')->user());
    }

    /**
     * Update data of the current logged in customer
     */
    public function update(Request $newData) {

    }

    /**
     * Delete the account of the current logged in customer
     */
    public function delete() {

    }

    /**
     * Log in a customer by email and password
     * In case of success the remember_token must be setted and returned in the resposne
     */
    public function login(Request $request) {
        $customer = Customer::attemptLogin($request->email, $request->password);
        if(isset($customer)) {
            $token = $customer->setApiToken();
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
            $message = ['message' => 'Logout'];
            $code = HttpResponseCode::OK;
        }
        else {
            $messaggio = ['message' => 'User not recognized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }
}
