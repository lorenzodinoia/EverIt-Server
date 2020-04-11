<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Restaurateur extends Authenticatable
{
    protected $guarded = ['password', 'remember_token', 'image_path', 'device_id'];
    protected $hidden = ['remember_token', 'password', 'device_id'];
    protected $with = ['city', 'shopType', 'openingTimes'];

    /**
     * Password field setter
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Define the inverse one (city) to many (restaurateurs) relationship
     */
    public function city() {
        return $this->belongsTo('App\City');
    }

    /**
     * Define the inverse one (shop type) to many (restaurateurs) relationship
     */
    public function shopType() {
        return $this->belongsTo('App\ShopType');
    }

    /**
     * Define the many (restaurateurs) to many (customers) relationship for feedbacks
     */
    public function feedbacks()
    {
        return $this->belongsToMany('App\Customer', 'restaurateur_feedback')->withPivot('vote');
    }

    /**
     * Define the inverse one (restaurateurs) to many (products) relationship
     */
    public function products()
    {
        return $this->hasManyThrough('App\Product', 'App\ProductCategory');
    }

    public function productCategories() {
        return $this->hasMany('App\ProductCategory');
    }

    public function orders() {
        return $this->hasMany('App\Order');
    }

    public function pendingOrders() {
        return $this->orders()->where('delivered', false);
    }

    public function deliveredOrders() {
        return $this->orders()->where('delivered', true);
    }

    public function openingTimes(){
        return $this->hasMany('App\OpeningTime');
    }

    /**
     * Check if the request is well formatted
     */
    public static function checkCreateRequest(Request $request) {
        $rules = [
            'shop_name' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'phone_number' => 'required|string|between:1,15',
            'email' => 'required|email',
            'password' => 'required|string',
            'vat_number' => 'required|string|between:1,11',
            'description' => 'string',
            'delivery_cost' => 'required|numeric',
            'min_price' => 'numeric',
            'shop_type_id' => 'required|integer',
            'city_id' => 'required|integer'
        ];

        $message = [
            'required' => ':attribute required',
            'string' => ':attribute must be string',
            'between' => ':attribute must be between :min and :max',
            'email' => ':attribute must respect email standard',
            'numeric' => ':attribute must be numeric',
            'integer' => ':attribute must be integer'
        ];

        return Validator::make($request->all(), $rules, $message);
    }

    public static function checkUpdateRequest(Request $request) {
        $rules = [
            'shop_name' => 'required|string',
            'address' => 'required|string',
            'phone_number' => 'required|string|between:1,15',
            'email' => 'required|email',
            'password' => 'string',
            'vat_number' => 'required|string|between:1,11',
            'description' => 'string',
            'delivery_cost' => 'required|numeric',
            'min_price' => 'numeric',
            'shop_type_id' => 'required|integer',
            'city_id' => 'required|integer'
        ];

        $message = [
            'required' => ':attribute required',
            'string' => ':attribute must be string',
            'between' => ':attribute must be between :min and :max',
            'email' => ':attribute must respect email standard',
            'numeric' => ':attribute must be numeric',
            'integer' => ':attribute must be integer'
        ];

        return Validator::make($request->all(), $rules, $message);
    }
    /**
     * Set the remember_token
     */
    public function setApiToken() {
        $token = Str::random(60);

        $this->makeVisible('remember_token')->remember_token = $token;
        $this->makeHidden('remember_token');
        $this->save();

        return $token;
    }

    /**
     * Invalidate the remember_token
     */
    public function removeApiToken() {
        $this->makeVisible('remember_token')->remember_token = null;
        $this->makeHidden('remember_token');
        $this->save();
    }

    /**
     * Attempt login retriving a restaurateur by email and password
     * If email and password are correct, the restaurateur is returned. Returns null otherwise
     */
    public static function attemptLogin($email, $password) {
        $restaurateur = Restaurateur::where('email', $email)->first();
        if(isset($restaurateur) && Hash::check($password, $restaurateur->makeVisible('password')->password)) {
            return $restaurateur->makeHidden('password');
        }
        else {
            return null;
        }
    }

    /**
     * Set the Android app id in order to send notification
     */
    public function setDeviceId($deviceId) {
        $this->makeVisible('device_id')->device_id = $deviceId;
        $this->makeHidden('device_id');
        $this->save();
    }

    /**
     * Invalidate the device_id
     */
    public function removeDeviceId() {
        $this->makeVisible('device_id')->device_id = null;
        $this->makeHidden('device_id');
        $this->save();
    }

    /**
     * Send notification to restaurateur's device
     */
    public function sendNotification($title, $message) {
        $this->makeVisible('device_id');

        if(isset($this->device_id)) {
            $notification = new Notification($this->device_id, $title, $message);
            $result = $notification->send()['success'];
            if($result == 1) {
                $result = true;
            }
            else {
                $result = false;
            }
            $this->makeHidden('device_id');
        }
        else {
            $result = false;
        }

        return $result;
    }
}
