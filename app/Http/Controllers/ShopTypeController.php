<?php

namespace App\Http\Controllers;

use App\HttpResponseCode;
use App\ShopType;
use Illuminate\Http\Request;

class ShopTypeController extends Controller
{
    /**
     * Get details for a given shop type
     */
    public function read($id) {
        $shopType = ShopType::find($id);
        if(isset($shopType)){
            $message = $shopType;
            $code = HttpResponseCode::OK;
        }
        else{
            $message = "Shop type not found";
            $code = HttpResponseCode::NOT_FOUND;
        }

        return response()->json($message, $code);
    }

    /**
     * Get all shop types
     */
    public function readAll() {
        return response()->json(ShopType::all(), HttpResponseCode::OK);
    }
}
