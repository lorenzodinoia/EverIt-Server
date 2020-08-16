<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Review extends Model
{
    public $timestamps = true;
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    public function restaurateur() {
        return $this->belongsTo('App\Restaurateur');
    }

    public function customer() {
        return $this->belongsTo('App\Customer');
    }

    public static function checkCreateRequest(Request $request) {
        $rules = [
            'vote' => 'required|integer',
            'text' => 'required|string'
        ];

        $message = [
            'required' => ':attribute required',
            'string' => ':attribute must be string',
            'integer' => ':attribute must be integer'
        ];

        return Validator::make($request->all(), $rules, $message);
    }
}
