<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Order extends Model
{
    const STATUS_ORDERED = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_DELIVERING = 3;
    const STATUS_DELIVERED = 4;

    protected $with = ["products"];
    protected $casts = [
        'estimated_delivery_time'  => 'datetime:Y-m-d H:i',
        'actual_delivery_time' => 'datetime:Y-m-d H:i',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
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
     *
     */
    public function restaurateur() {
        return $this->belongsTo('App\Restaurateur');
    }

    public function proposals() {
        return $this->hasMany('App\Proposal');
    }

    /**
     * Get the restaurateur as field
     */
    public function getRestaurateurAttribute()
    {
        return $this->restaurateur()->get()[0];
    }

    /**
     * Check if the request is well formatted
     */
    public static function checkCreateRequest(Request $request) {
        $rules = [
            'delivery_address' => 'required|string',
            'estimated_delivery_time' => 'required|date_format:H:i',
            'products.*.id' => 'required|integer',
            'products.*.quantity' => 'integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ];

        $message = [
            'required' => ':attribute required',
            'string' => ':attribute must be string',
            'date_format' => ':attribute must respect time format (H:i)',
            'integer' => ':attribute must be integer',
            'numeric' => ':attribure must be numeric'
        ];

        return Validator::make($request->all(), $rules, $message);
    }
}
