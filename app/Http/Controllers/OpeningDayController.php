<?php

namespace App\Http\Controllers;

use App\HttpResponseCode;
use App\OpeningDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OpeningDayController extends Controller
{
    public function attach(Request $request) {
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)) {
            if(isset($request->day) && isset($request->opening_time) && isset($request->closing_time)) {
                $day = OpeningDay::find($request->day);
                if(isset($day)) {
                    $restaurateur->openingDays()->attach($request->day, ['opening_time' => $request->opening_time, 'closing_time' => $request->closing_time]);
                    $message = ['message' => 'Ok'];
                    $code = HttpResponseCode::OK;
                }
            }
            else {
                $message = ['message' => 'Data not provided'];
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else {
            $message = ['message' => 'User not recognized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function detach($id) {
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)) {
            $restaurateur->openingDays()->wherePivot('id', '=', $id)->detach();
            $message = ['message' => 'Ok'];
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ['message' => 'User not recognized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }
}
