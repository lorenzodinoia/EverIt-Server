<?php

namespace App\Http\Controllers;

use App\HttpResponseCode;
use App\Order;
use App\Product;
use App\Restaurateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Create new order
     */
    public function create(Request $request) {
        $customer = Auth::guard('customer')->user();
        if(isset($customer)) {
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
                //$order->validation_code = $request->validation_code; Dovremmo generarlo
                $order->validation_code = (string) rand(10000, 99999);
                if(isset($request->actual_delivery_time)){
                    $order->actual_delivery_time = $request->actual_delivery_time;
                }
                if(isset($request->delivered)){
                    $order->delivered = $request->delivered;
                }
                $order->customer()->associate($customer->id);
                if(isset($request->rider_id)){
                    $order->rider()->associate($request->rider_id);
                }

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
                    $restaurateur = $savedOrder->restaurateur()->get();
                    if(isset($restaurateur)) {
                        $restaurateur->sendNotification('Nuovo ordine', 'Hai ricevuto un nuovo ordine');
                    }
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
        else{
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
            return response()->json($customer->orders()->get()->each(function ($order) {
                $order->append('restaurateur');
            }), HttpResponseCode::OK);
        }
        else{
            return response()->json("Unauthorized", HttpResponseCode::UNAUTHORIZED);
        }
    }

    /**
     * Get the list of in progress order for the current logged in customer
     */
    public function readCustomerInProgressOrders() {

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

     /**
     * Get the list of in progress order for the current logged in restaurateur
     */
    public function readRestaurateurInProgressOrders() {
        $restaurateur = Auth::guard("restaurateur")->user();

        if(isset($restaurateur)) {
            return response()->json($restaurateur->pending_orders, HttpResponseCode::OK);
        }
        else{
            return response()->json("Unauthorized", HttpResponseCode::UNAUTHORIZED);
        }
    }

    /**
     * Mark an order as in delivering
     * The restaurateur must be logged in
     */
    public function markAsInDelivering($orderId) {

    }
}
