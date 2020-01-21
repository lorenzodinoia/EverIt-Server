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
     * Define the many (customers) to many (restaurateurs) relationship for feedbacks
     */
    public function feedbacks()
    {
        return $this->belongsToMany('App\Restaurateur', 'restaurateur_feedback')->withPivot('vote');
    }

    /**
     * Define the one (customer) to many (orders) relationship
     */
    public function orders() {
        return $this->hasMany('App\Order');
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
            'password' => 'required|string',
        ];
        $message = [
          'required' => ':attribute required',
          'string' => ':attribute must be string',
          'between' => ':attribute must be between :min and :max',
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
}
