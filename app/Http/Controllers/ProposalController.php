<?php

namespace App\Http\Controllers;

use App\HttpResponseCode;
use App\Order;
use App\Proposal;
use App\Restaurateur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ProposalController extends Controller
{
    public function read() {
        $rider = Auth::guard('rider')->user();

        if(isset($rider)) {
            $message = $rider->proposals()->get();
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ['message' => 'Unauthorized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function acceptProposal($id){
        $rider = Auth::guard('rider')->user();
        if(isset($rider)){
            $proposal = Proposal::find($id);
            if(isset($proposal)){
                if($proposal->rider_id == $rider->id) {
                    $order = Order::find($proposal->order_id);
                    $order->rider_id = $rider->id;
                    $order->save();
                    $deleted = $order->proposals()->delete();
                    if ($deleted) {
                        $restaurateur = Restaurateur::find($proposal->restaurateur_id);
                        $notificationMessage = "E' stato trovato un rider disponibile a cui assegnare l'ordine";
                        $restaurateur->sendNotification('Rider assegnato', $notificationMessage);
                        $message = ['message' => 'Ok'];
                        $code = HttpResponseCode::OK;
                    } else {
                        $message = ['message' => "Can't delete proposals"];
                        $code = HttpResponseCode::SERVER_ERROR;
                    }
                }
                else{
                    $message = ['message' => "Proposal not assigned to this rider"];
                    $code = HttpResponseCode::BAD_REQUEST;
                }
            }
            else{
                $message = ['message' => 'Expiried'];
                $code = HttpResponseCode::NOT_FOUND;
            }
        }
        else{
            $message = ['message' => 'Unauthorized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function refuseProposal($id){
        $rider = Auth::guard('rider')->user();
        if(isset($rider)){
            $proposal = Proposal::find($id);
            if(isset($proposal)){
                $deleted = $proposal->delete();
                if($deleted){
                    $message = ['message' => 'Deleted'];
                    $code = HttpResponseCode::OK;
                }
                else{
                    $message = ['message' => "Can't delete proposal"];
                    $code = HttpResponseCode::SERVER_ERROR;
                }
            }
        }
        else{
            $message = ['message' => 'Unauthorized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }
}
