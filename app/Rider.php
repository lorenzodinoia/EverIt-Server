<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


class Rider extends Authenticatable
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
     * Define the one (rider) to many (orders) relationship
     */
    public function orders() {
        return $this->hasMany('App\Order');
    }

    /**
     * Check if the request is well formatted using the method of Customer model because they have the same structure
     */
    public static function checkCreateRequest(Request $request) {
        return Customer::checkCreateRequest($request);
    }

    /**
     * Check if the request is well formatted using the method of Customer model because they have the same structure
     */
    public static function checkUpdateRequest(Request $request) {
        return Customer::checkUpdateRequest($request);
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
     * Attempt login retriving a customer by email and password
     * If email and password are correct, the customer is returned. Returns null otherwise
     */
    public static function attemptLogin($email, $password) {
        $rider = Rider::where('email', $email)->first();
        if(isset($rider) && Hash::check($password, $rider->makeVisible('password')->password)) {
            return $rider->makeHidden('password');
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
