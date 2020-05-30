<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Customer extends Authenticatable
{
    protected $guarded = ['password', 'remember_token', 'device_id'];
    protected $hidden = ['remember_token', 'password', 'device_id'];

    /**
     * Password field setter
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Define the many (customers) to many (restaurateurs) relationship for feedbacks
     */
    public function feedbacks()
    {
        return $this->hasMany('App\Feedback')->with("restaurateur");
        //return $this->belongsToMany('App\Restaurateur', 'restaurateur_feedback')->withPivot('vote');
    }

    /**
     * Define the one (customer) to many (orders) relationship
     */
    public function orders() {
        return $this->hasMany('App\Order');
    }

    public function restaurateur(){
        return $this->hasManyThrough('App\Restaurateur', 'App\Feedback');
    }

    /**
     * Check if the request is well formatted
     */
    public static function checkCreateRequest(Request $request) {
        $rules = [
            'name' => 'required|string|between:1,50',
            'surname' => 'required|string|between:1,50',
            'phone_number' => 'required|string|between:1,15',
            'email' => 'required|email',
            'password' => 'required|string|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
        ];
        $message = [
          'required' => ':attribute required',
          'string' => ':attribute must be string',
          'between' => ':attribute must be between :min and :max',
          'email' => ':attribute must respect email standard',
            'regex' => ':attribute must respect format'
        ];

        return Validator::make($request->all(), $rules, $message);
    }

    /**
     * Check if the request is well formatted
     */
    public static function checkUpdateRequest(Request $request) {
        $rules = [
            'name' => 'required|string|between:1,50',
            'surname' => 'required|string|between:1,50',
            'phone_number' => 'required|string|between:1,15',
            'email' => 'required|email',
            'password' => 'string|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
        ];
        $message = [
            'required' => ':attribute required',
            'string' => ':attribute must be string',
            'between' => ':attribute must be between :min and :max',
            'email' => ':attribute must respect email standard',
            'regex' => ':attribute must respect format'
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
        $this->save();
    }

    /**
     * Attempt login retriving a customer by email and password
     * If email and password are correct, the customer is returned. Returns null otherwise
     */
    public static function attemptLogin($email, $password) {
        $customer = Customer::where('email', $email)->first();
        if(isset($customer) && Hash::check($password, $customer->makeVisible('password')->password)) {
            return $customer->makeHidden('password');
        }
        else {
            return null;
        }
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
     * Send notification to customer's device
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
