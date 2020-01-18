<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illimunate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Product extends Model
{
    /**
     * Define the inverse one (restaurateurs) to many (products) relationship
     */
    public function restaurateur() {
        return $this->belongsTo('App\Restaurateur');
    }

    /**
     * Define the inverse one (product) to many (product_category) relationship
     */
    public function productCategory() {
        return $this->belongsTo('App\ProductCategory');
    }

    /**
     * Check if the request is well formatted
     */
    public static function checkCreateRequest(Request $request) {
        $rules = [
            'name' => 'required|string',
            'price' => 'required|numeric',
            'product_category_id' => 'required|integer',
            'restaurateur_id' => 'required|integer'
        ];

        return (!Validator::make($request->all(), $rules)->fails());  
    }
}
