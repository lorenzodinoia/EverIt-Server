<?php

namespace App;

use Carbon\Carbon;
use Carbon\Traits\Date;
use DateTime;
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
    protected $with = ['shopType'];
    protected $appends = ['is_open', 'avg'];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    /**
     * Password field setter
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Define the inverse one (shop type) to many (restaurateurs) relationship
     */
    public function shopType() {
        return $this->belongsTo('App\ShopType');
    }

    /**
     * Define the many (restaurateurs) to many (customers) relationship for reviews
     */
    public function reviews()
    {
        return $this->hasMany('App\Review')->with('customer');
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
        return $this->orders()->where('status', 0);
    }

    public function toDoOrders() {
        return $this->orders()->where('status', 1);
    }

    public function deliveredOrders() {
        return $this->orders()->where('status', '>=', 2);
    }

    public function openingTimes(){
        return $this->hasMany('App\OpeningTime');
    }

    public function getIsOpenAttribute() {
        $openingTimes = $this->openingTimes()->get();
        $now = Carbon::now();

        if (isset($openingTimes)) {
            $now = new DateTime();
            $day = (int) $now->format('N');

            foreach ($openingTimes as $time) {
                if (isset($time->openingDay)) {
                    if ($time->openingDay['id'] == $day) {
                        try {
                            $openingTime = new DateTime($time->opening_time);
                            $closingTime = new DateTime($time->closing_time);
                        }
                        catch (\Exception $e) {
                            return false;
                        }
                        if ($now >= $openingTime && $now <= $closingTime) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function getAvgAttribute(){
        $reviews = $this->reviews()->get();
        if(isset($reviews[0])){
            $count = 0.0;
            $n = sizeof($reviews);
            foreach($reviews as $review){
                $count += $review->vote;
            }

            return round($count/$n);
        }
        else{
            return 0;
        }
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
            'password' => 'required|string|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
            'vat_number' => 'required|string|between:1,11',
            'max_delivery_time_slot' => 'required|numeric',
            'delivery_cost' => 'required|numeric',
            'min_price' => 'numeric',
            'shop_type_id' => 'required|integer',
            'opening_times' => 'required',
            'opening_times.*.id' => 'required'
        ];

        $message = [
            'required' => ':attribute required',
            'string' => ':attribute must be string',
            'between' => ':attribute must be between :min and :max',
            'email' => ':attribute must respect email standard',
            'numeric' => ':attribute must be numeric',
            'integer' => ':attribute must be integer',
            'regex' => ':attribute must respect format',
            'date_format' => ':attribute must respect time format (H:i)',
        ];

        return Validator::make($request->all(), $rules, $message);
    }

    public static function checkUpdateRequest(Request $request) {
        $rules = [
            'shop_name' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'phone_number' => 'required|string|between:1,15',
            'email' => 'required|email',
            'password' => 'string|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
            'vat_number' => 'required|string|between:1,11',
            'max_delivery_time_slot' => 'required|numeric',
            'delivery_cost' => 'required|numeric',
            'min_price' => 'numeric',
            'shop_type_id' => 'required|integer',
        ];

        $message = [
            'required' => ':attribute required',
            'string' => ':attribute must be string',
            'between' => ':attribute must be between :min and :max',
            'email' => ':attribute must respect email standard',
            'numeric' => ':attribute must be numeric',
            'integer' => ':attribute must be integer',
            'regex' => ':attribute must respect format'
        ];

        return Validator::make($request->all(), $rules, $message);
    }

    public static function checkUpdateShopName(Request $request){
        $rules = [
          'shop_name' => 'required|string'
        ];

        $message = [
            'required' => ':attribute required',
            'string' => ':attribute must be string'
        ];

        return Validator::make($request->all(), $rules, $message);
    }

    public static function checkUpdateEmail(Request $request){
        $rules = [
            'email' => 'required|email'
        ];

        $message = [
            'required' => ':attribute required',
            'email' => ':attribute must respect email standard'
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

    public function changePassword($oldPassword, $newPassword) {
        if(Hash::check($oldPassword, $this->makeVisible('password')->password)) {
            $this->password = $newPassword;
            $this->makeHidden('password');
            return true;
        }
        else {
            return false;
        }
    }
}
