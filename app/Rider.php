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
    protected $guarded = ['password', 'remember_token'];
    protected $hidden = ['remember_token', 'password'];

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
}
