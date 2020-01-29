<?php

namespace App\Http\Controllers;

use App\City;
use App\HttpResponseCode;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * Get details for a given city
     */
    public function read($id) {
        $city = City::find($id);
        if(isset($city)){
            return response()->json($city, HttpResponseCode::OK);
        }
        else{
            return response()->json("City not found", HttpResponseCode::NOT_FOUND);
        }
    }

    /**
     * Get all cities
     */
    public function readAll() {
        return response()->json(City::all(), HttpResponseCode::OK);
    }
}
