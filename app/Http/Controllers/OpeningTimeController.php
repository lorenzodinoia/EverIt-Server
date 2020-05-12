<?php

namespace App\Http\Controllers;

use App\HttpResponseCode;
use App\OpeningDay;
use App\OpeningTime;
use App\Restaurateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OpeningTimeController extends Controller
{
    /**
     * Create new opening time for a restaurateur
     * Restaurateur must be logged in
     */
    public function create(Request $request) {
        if(isset($request->opening_time) && isset($request->closing_time) && isset($request->day)) {
            $restaurateur = Auth::guard('restaurateur')->user();
            if(isset($restaurateur)) {
                $day = OpeningDay::find($request->day);
                if(isset($day)) {
                    $openingTime = new OpeningTime;
                    $openingTime->opening_time = $request->opening_time;
                    $openingTime->closing_time = $request->closing_time;
                    $openingTime->restaurateur()->associate($restaurateur);
                    $openingTime->openingDay()->associate($day);
                    $openingTime->save();

                    $message = ['message' => 'Created'];
                    $code = HttpResponseCode::CREATED;
                }
                else {
                    $message = ['message' => 'Day not found'];
                    $code = HttpResponseCode::NOT_FOUND;
                }
            }
            else {
                $message = ['message' => 'Restaurateur not found'];
                $code = HttpResponseCode::NOT_FOUND;
            }
        }
        else {
            $message = ['message' => 'Data not provided'];
            $code = HttpResponseCode::BAD_REQUEST;
        }

        return response()->json($message, $code);
    }


    /**
     * Delete an opening time of a restaurateur
     * Restaurateur must be logged in
     */
    public function delete(Request $request, $openingTimeId) {
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)) {
            $openingTime = $restaurateur->openingTimes()->where('id', $openingTimeId)->first()->get();
            if(isset($openingTime)) {
                $openingTime[0]->delete();

                $message = ['message' => 'Deleted'];
                $code = HttpResponseCode::OK;
            }
            else {
                $message = ['message' => 'Opening time not found'];
                $code = HttpResponseCode::OK;
            }
        }
        else {
            $message = ['message' => 'User not recognized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }
}
