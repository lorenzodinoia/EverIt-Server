<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Order extends Model
{
    protected $casts = [
        'estimated_delivery_time'  => 'datetime:Y-m-d H:i',
        'actual_delivery_time' => 'datetime:Y-m-d H:i',
    ];

    /**
     * Define the many (orders) to many (products) relationship
     */
    public function products() {
        return $this->belongsToMany('App\Product', 'order_product')->withPivot('quantity');
    }

    /**
     * Define the one (customer) to many (orders) relationship
     */
    public function customer() {
        return $this->belongsTo('App\Customer');
    }

    /**
     * Define the inverse many (orders) to one (rider) relationship
     */
    public function rider() {
        return $this->belongsTo('App\Rider');
    }

    /**
     * Get the reference to the restaurateur through products
     */
    public function restaurateur() {
        return $this->products()->first()->restaurateur()->first();
    }

    /**
     * Get the restaurateur as field
     */
    public function getRestaurateurAttribute()
    {
        return $this->restaurateur()->get();
    }

    /**
     * Check if the request is well formatted
     */
    public static function checkCreateRequest(Request $request) {
        $rules = [
            'delivery_address' => 'required|string',
            'estimated_delivery_time' => 'required|date_format:Y-m-d H:i',
            'products.*.id' => 'required|integer',
            'products.*.quantity' => 'integer'
            //'validation_code' => 'required|integer' In teoria dovrebbe generarlo il server
        ];

        $message = [
            'required' => ':attribute required',
            'string' => ':attribute must be string',
            'date_format' => ':attribute must respect date format (Y-m-d H:m:s)',
            'integer' => ':attribute must be integer'
        ];

        return Validator::make($request->all(), $rules, $message);
    }
}
