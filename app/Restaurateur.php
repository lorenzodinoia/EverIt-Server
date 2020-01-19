<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Restaurateur extends Authenticatable
{
    protected $guarded = ['password', 'remember_token', 'image_path'];
    protected $hidden = ['remember_token', 'password'];

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
        return $this->hasMany('App\Product');
    }

    /**
     * Check if the request is well formatted
     */
    public static function checkCreateRequest(Request $request) {
        $rules = [
            'shop_name' => 'required|string',
            'address' => 'required|string',
            'cap' => 'required|string|between:1,5',
            'phone_number' => 'required|string|between:1,15',
            'email' => 'required|email',
            'password' => 'required|string',
            'piva' => 'required|string|between:1,11',
            'description' => 'string',
            'delivery_cost' => 'numeric',
            'min_quantity' => 'numeric',
            'order_range_time' => 'numeric',
            'shop_type_id' => 'required|integer',
            'city_id' => 'required|integer'
        ];

        return (!Validator::make($request->all(), $rules)->fails());        
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
}
