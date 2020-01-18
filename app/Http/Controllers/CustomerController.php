<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;
use App\HttpResponseCode;

class CustomerController extends Controller
{
    /**
     * Create new customer
     */
    public function create(Request $request) {
        if (Customer::checkCreateRequest($request)) {

        }
        else {
            return response()->json(null, HttpResponseCode::BAD_REQUEST);
        }
    }

    /**
     * Get details of the current logged in customer
     */
    public function readCurrent() {

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
    public function login($email, $password) {

    }

    /**
     * Log out the current logged in customer
     * In case of success the remeber_token must be removed
     */
    public function logout() {

    }
}
