<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Create new order
     */
    public function create(Request $request) {
        if (Order::checkCreateRequest($request)) {

        }
        else {
            return response()->json(null, HttpResponseCode::BAD_REQUEST);
        }
    }

    /**
     * Get details for a given order
     */
    public function read($id) {

    }

    /**
     * Get current logged in customer's order list
     */
    public function readCustomerOrders() {

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

    }

     /**
     * Get the list of in progress order for the current logged in restaurateur
     */
    public function readRestaurateurInProgressOrders() {

    }

    /**
     * Mark an order as in delivering
     * The restaurateur must be logged in
     */
    public function markAsInDelivering($orderId) {

    }
}
