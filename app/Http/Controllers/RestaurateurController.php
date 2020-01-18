<?php

namespace App\Http\Controllers;

use App\Restaurateur;
use Illuminate\Http\Request;

class RestaurateurController extends Controller
{
    /**
     * Create new restaurateur
     */
    public function create(Request $request) {
        if (Restaurateur::checkCreateRequest($request)) {

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
    public function login($email, $password) {

    }

    /**
     * Log out the current logged in restaurateur
     * In case of success the remeber_token must be removed
     */
    public function logout() {

    }
}
