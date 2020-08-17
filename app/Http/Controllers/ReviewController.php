<?php

namespace App\Http\Controllers;

use App\Review;
use App\HttpResponseCode;
use App\Restaurateur;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use const http\Client\Curl\Features\HTTP2;

class ReviewController extends Controller
{
    public function create(Request $request, $idRestaurateur){
        $customer = Auth::guard('customer')->user();
        if(isset($customer)){
            $feedback = $customer->reviews()->where('restaurateur_id', '=', $idRestaurateur)->first();
            if(!isset($feedback)){
                $validator = Review::checkCreateRequest($request);
                if(!$validator->fails()) {
                    $newFeedback = new Review();
                    $newFeedback->vote = $request->vote;
                    $newFeedback->text = $request->text;
                    $newFeedback->restaurateur()->associate($idRestaurateur);
                    $newFeedback->customer()->associate($customer->id);
                    $newFeedback->save();
                    $message = $newFeedback;
                    $code = HttpResponseCode::OK;
                }
                else{
                    $message = $validator->errors();
                    $code = HttpResponseCode::BAD_REQUEST;
                }
            }
            else{
                $message = ["message" => "Can't upload more than one feedback per restaurateur"];
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else{
            $message = ["message" => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function update(Request $request, $id){
        $customer = Auth::guard('customer')->user();
        if(isset($customer)){
            $feedback = Review::find($id);
            if(isset($feedback)){
                if($feedback->customer_id == $customer->id){
                    $validator = Review::checkCreateRequest($request);
                    if(!$validator->fails()){
                        $feedback->vote = $request->vote;
                        $feedback->text = $request->text;
                        $feedback->save();
                        $message = $feedback;
                        $code = HttpResponseCode::OK;
                    }
                    else{
                        $message = $validator->errors();
                        $code = HttpResponseCode::BAD_REQUEST;
                    }
                }
                else{
                    $message = ["message" => "Can't edit others feedback"];
                    $code = HttpResponseCode::BAD_REQUEST;
                }
            }
            else{
                $message = ["message" => "Can't find feedback"];
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else{
            $message = ["message" => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function delete($id){
        $customer = Auth::guard('customer')->user();
        if(isset($customer)){
            $feedback = $customer->reviews()->where('id', $id)->get();
            if(isset($feedback[0])){
                $deleted = $feedback[0]->delete();
                if($deleted){
                    $message = ["message" => "Deleted"];
                    $code = HttpResponseCode::OK;
                }
                else{
                    $message = ["message" => "Can't delete feedback"];
                    $code = HttpResponseCode::SERVER_ERROR;
                }
            }
            else{
                $message = ["message" => "Can't find feedback"];
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else{
            $message = ["message" => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function readRestaurateurReviewsById($id) {
        $restaurateur = Restaurateur::find($id);
        if(isset($restaurateur)){
            $message = $restaurateur->reviews()->get();
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ["message" => "Restaurateur not found"];
            $code = HttpResponseCode::NOT_FOUND;
        }

        return response()->json($message, $code);
    }

    public function readRestaurateurReviews() {
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)){
            $message = $restaurateur->reviews()->get();
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ["message" => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function readCustomerReviews() {
        $customer = Auth::guard('customer')->user();
        if(isset($customer)) {
            $message = $customer->reviews()->get();
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ["message" => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

}
