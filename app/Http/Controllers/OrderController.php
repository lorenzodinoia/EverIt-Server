<?php

namespace App\Http\Controllers;

use App\HttpResponseCode;
use App\Order;
use App\Product;
use App\Restaurateur;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class OrderController extends Controller
{
    /**
     * Create new order
     */
    public function create(Request $request, $restaurateurId) {
        $customer = Auth::guard('customer')->user();
        $restaurateur = Restaurateur::find($restaurateurId);
        if(isset($customer) && isset($restaurateur)) {
            $validator = Order::checkCreateRequest($request);
            if (!$validator->fails()) {
                $order = new Order();
                $order->delivery_address = $request->delivery_address;
                $order->estimated_delivery_time = $request->estimated_delivery_time;
                if(isset($request->order_notes)){
                    $order->order_notes = $request->order_notes;
                }
                if(isset($request->delivery_notes)){
                    $order->delivery_notes = $request->delivery_notes;
                }
                $order->validation_code = (string) rand(10000, 99999);
                if(isset($request->actual_delivery_time)){
                    $order->actual_delivery_time = $request->actual_delivery_time;
                }
                if(isset($request->delivered)){
                    $order->delivered = $request->delivered;
                }
                if(isset($request->late)){
                    $order->late = $request->late;
                }
                $order->customer()->associate($customer->id);
                if(isset($request->rider_id)){
                    $order->rider()->associate($request->rider_id);
                }
                $order->restaurateur()->associate($restaurateur);

                $order->save();
                $savedOrder = Order::find($order->id);

                $productAttached = true;
                foreach($request->products as $product) {
                    $id = $product['id'];
                    $quantity = (isset($product['quantity'])) ? intval($product['quantity']) : 1;
                    $selectedProduct = Product::find($id);
                    if(isset($selectedProduct)) {
                        $order->products()->attach($id, ['quantity' => $quantity]);
                    }
                    else {
                        $productAttached = false;
                    }
                }

                if($productAttached) {
                    $restaurateur->sendNotification('Nuovo ordine', 'Hai ricevuto un nuovo ordine');
                    $message = $savedOrder;
                    $code = HttpResponseCode::OK;
                }
                else {
                    $savedOrder->delete();
                    $message = ['message' => 'Invalid products id'];
                    $code = HttpResponseCode::BAD_REQUEST;
                }

            }
            else {
                $message = $validator->errors();
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else {
            $message = "Unauthorized";
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Get details for a given order
     */
    public function read($id) {
        $customer = Auth::guard("customer")->user();
        $order = Order::find($id);
        if($customer->id == $order->customer_id){
            return response()->json($order, HttpResponseCode::OK);
        }
        else{
            return response()->json("Unauthorized", HttpResponseCode::UNAUTHORIZED);
        }
    }

    /**
     * Get current logged in customer's order list
     */
    public function readCustomerOrders() {
        $customer = Auth::guard("customer")->user();

        if(isset($customer)) {
            return response()->json($customer->orders()->with('products')->get()->each(function ($order) {
                $order->append('restaurateur');
            }), HttpResponseCode::OK);
        }
        else{
            return response()->json("Unauthorized", HttpResponseCode::UNAUTHORIZED);
        }
    }

    /**
     * Mark an order as delivered by ID and validation code
     * The rider must be logged in
     */
    public function close($id, $validationCode) {

    }

    /**
     *  Get current logged in restaurateur's order list
     */
    public function readRestaurateurOrders() {
        $restaurateur = Auth::guard("restaurateur")->user();

        if(isset($restaurateur)) {
            return response()->json($restaurateur->delivered_orders, HttpResponseCode::OK);
        }
        else{
            return response()->json("Unauthorized", HttpResponseCode::UNAUTHORIZED);
        }
    }

    public function readRestaurateurDeliveredOrders() {
        $restaurateur = Auth::guard("restaurateur")->user();

        if(isset($restaurateur)) {
            return response()->json($restaurateur->deliveredOrders()->get(), HttpResponseCode::OK);
        }
        else {
            return response()->json(["message" => "Unauthorized"], HttpResponseCode::UNAUTHORIZED);
        }
    }

     /**
     * Get the list of in progress order for the current logged in restaurateur
     */
    public function readRestaurateurPendingOrders() {
        $restaurateur = Auth::guard("restaurateur")->user();

        if(isset($restaurateur)) {
            return response()->json($restaurateur->pendingOrders()->get(), HttpResponseCode::OK);
        }
        else {
            return response()->json(["message" => "Unauthorized"], HttpResponseCode::UNAUTHORIZED);
        }
    }

    /**
     * Mark an order as in delivering
     * The restaurateur must be logged in
     */
    public function markAsInDelivering($orderId) {

    }

    /**
     * Generates a set of times which can be used to delivery customer's orders
     */
    public function getAvailableDeliveryTime($restaurateurId) {
        $restaurateur = Restaurateur::find($restaurateurId);
        if(isset($restaurateur)) {
            $now = new DateTime();
            $nowDay = (int) $now->format('d');
            $nowMonth = (int) $now->format('m');
            $nowYear = (int) $now->format('Y');
            $day = (int) $now->format('N'); //Days of week: 1 for Monday, 2 for Tuesday, etc...

            $openingTimes = $restaurateur->openingTimes()->whereHas('openingDay', function (Builder $query) use ($day) {
                $query->where('id', $day);
            })->orderBy('opening_time')->get();

            if((isset($openingTimes)) && (count($openingTimes) >= 1)) {
                $message = array();
                foreach($openingTimes as $time) {
                    $start = new DateTime($time->opening_time);
                    $start->setDate($nowYear, $nowMonth, $nowDay);
                    if($start < $now) {
                        $start = clone $now;
                    }
                    $startMinutes = (int) $start->format('i');
                    $startHour = (int) $start->format('H');

                    $end = new DateTime($time->closing_time);
                    $end->setDate($nowYear, $nowMonth, $nowDay);
                    $endMinutes = (int) $end->format('i');
                    $endHour = (int) $end->format('H');

                    if($endHour < $startHour) {
                        //It means that the closing time is during the night of the next day
                        $end->add(new DateInterval('P1D'));
                    }

                    if($now >= $end) {
                        //Ignore current opening time
                        continue;
                    }

                    if(($startMinutes != 0) && ($startMinutes != 30)) {
                        //Round the minutes to 30 or 0
                        if($startMinutes <= 15) {
                            $start->setTime($startHour, 0);
                        }
                        else if(($startMinutes < 30) || (($startMinutes > 30) && ($startMinutes <= 45))) {
                            $start->setTime($startHour , 30);
                        }
                        else if($startMinutes > 45) {
                            $start->setTime($startHour + 1, 0);
                        }
                    }

                    $hoursDifference = (int) $start->diff($end)->format('%H');
                    $minutesDifference = (int) $start->diff($end)->format('%i');
                    $count = (int) $hoursDifference * 2;
                    $count += (int) floor($minutesDifference / 30);

                    for($i = 0; $i < $count; $i++) {
                        //Generates the delivery times
                        $newTime = $start->add(new DateInterval('PT30M'))->format('H:i');
                        array_push($message, $newTime);
                    }
                }

                if(count($message) == 0) {
                    $message = ['message' => 'No times available'];
                    $code = HttpResponseCode::NO_CONTENT;
                }
                else {
                    $code = HttpResponseCode::OK;
                }
            }
            else {
                $message = ['message' => 'The restaurant is closed today'];
                $code = HttpResponseCode::NO_CONTENT;
            }
        }
        else {
            $message = ['message' => 'Restaurateur not found'];
            $code = HttpResponseCode::NOT_FOUND;
        }

        return response()->json($message, $code);
    }
}
