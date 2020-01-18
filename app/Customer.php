<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illimunate\Http\Request;
use Illuminate\Support\Facades\Validator;


class Customer extends Model
{
    protected $guarded = ['password', 'remeber_token'];
    protected $hidden = ['remeber_token', 'password'];

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

        return (!Validator::make($request->all(), $rules)->fails());        
    }
}
