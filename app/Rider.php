<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illimunate\Http\Request;
use Illuminate\Support\Facades\Validator;


class Rider extends Model
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
}
