<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Order extends Model
{
    protected $casts = [
        'estimated_delivery_time'  => 'datetime',
        'actual_delivery_time' => 'datetime',
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
        return $this->products()->first()->restaurateur();
    }

    /**
     * Check if the request is well formatted
     */
    public static function checkCreateRequest(Request $request) {
        $rules = [
            'delivery_address' => 'required|string',
            'estimated_delivery_time' => 'required|date_format:Y-m-d H:i:s',
            'validation_code' => 'required|integer'
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
