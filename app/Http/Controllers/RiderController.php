<?php

namespace App\Http\Controllers;

use App\Rider;
use Illuminate\Http\Request;

class RiderController extends Controller
{
    /**
     * Create new rider
     */
    public function create(Request $request) {
        if (Rider::checkCreateRequest($request)) {

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
    public function login($email, $password) {

    }

    /**
     * Log out the current logged in rider
     * In case of success the remeber_token must be removed
     */
    public function logout() {

    }
}
