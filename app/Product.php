<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Product extends Model
{
    protected $hidden = ['pivot'];
    protected $appends = ['quantity'];
    public $timestamps = false;

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

    public function order() {
        return $this->belongsToMany('App\Order', 'order_product')->withPivot('quantity');
    }

    public function getQuantityAttribute() {
        $this->makeVisible('pivot');
        $value = 0;
        if(isset($this->pivot)) {
            $value = $this->pivot->quantity;
            $this->makeHidden('pivot');
        }

        return $value;
    }

    /**
     * Check if the request is well formatted
     */
    public static function checkCreateRequest(Request $request) {
        $rules = [
            'name' => 'required|string',
            'price' => 'required|numeric',
            'details' => 'required|string'
        ];

        $message = [
            'required' => ':attribute required',
            'string' => ':attribute must be string',
            'numeric' => ':attribute must be numeric'
        ];
        return Validator::make($request->all(), $rules, $message);
    }

    public static function checkUpdateRequest(Request $request) {
        $rules = [
            'name' => 'required|string',
            'price' => 'required|numeric',
            'details' => 'required|string',
            'product_category_id' => 'required|integer'
        ];

        $message = [
            'required' => ':attribute required',
            'string' => ':attribute must be string',
            'numeric' => ':attribute must be numeric',
            'integer' => ':attribute must be integer'
        ];
        return Validator::make($request->all(), $rules, $message);
    }
}
