<?php

namespace App\Http\Controllers;

use App\Customer;
use App\HttpResponseCode;
use App\Order;
use App\Product;
use App\Restaurateur;
use DateInterval;
use DateTime;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private const CUSTOMER_ORDER_RELATIONSHIP = ['restaurateur'];
    private const RESTAURATEUR_ORDER_RELATIONSHIP = ['products', 'customer', 'rider'];
    /**
     * Create new order
     */
    public function create(Request $request, $restaurateurId) {
        $customer = Auth::guard('customer')->user();
        $restaurateur = Restaurateur::find($restaurateurId);

        if(isset($customer) && isset($restaurateur)) {
            $validator = Order::checkCreateRequest($request);
            if (!$validator->fails()) {
                DB::beginTransaction();

                try {
                    $order = new Order();
                    $order->delivery_address = $request->delivery_address;
                    $now = new DateTime();
                    $nowHour = (int) $now->format('H');
                    $deliveryDateTime = new DateTime($request->estimated_delivery_time);
                    $deliveryHour = (int) $deliveryDateTime->format('H');
                    if($deliveryHour < $nowHour) {
                        //It means that the order will be delivered during the night of the next day
                        $deliveryDateTime->add(new DateInterval('P1D'));
                    }
                    $order->estimated_delivery_time = $deliveryDateTime->format('Y-m-d H:i');
                    if(isset($request->order_notes)) {
                        $order->order_notes = $request->order_notes;
                    }
                    if(isset($request->delivery_notes)) {
                        $order->delivery_notes = $request->delivery_notes;
                    }
                    $order->validation_code = (string)rand(10000, 99999);//Generates 5 digits number as validation code
                    $order->customer()->associate($customer->id);
                    $order->restaurateur()->associate($restaurateur);
                    $order->save();

                    $productsCount = 0;
                    foreach($request->products as $product) {
                        $id = $product['id'];
                        $quantity = (isset($product['quantity'])) ? intval($product['quantity']) : 1;
                        $selectedProduct = Product::find($id);
                        if(isset($selectedProduct)) {
                            $order->products()->attach($id, ['quantity' => $quantity]);
                            $productsCount += $quantity;
                        }
                        else {
                            throw new \Exception("Product not found");
                        }
                    }
                }
                catch (\Exception $e) {
                    DB::rollback();
                    return response()->json(['message' => $e->getMessage()], HttpResponseCode::BAD_REQUEST);
                }

                DB::commit();
                $notificationFormat = "Hai ricevuto un nuovo ordine di %d prodotti da consegnare alle ore %s";
                $notificationMessage = sprintf($notificationFormat, $productsCount, $deliveryDateTime->format('H:i'));
                $restaurateur->sendNotification('Nuovo ordine', $notificationMessage);

                $message = Order::find($order->id)->with(OrderController::CUSTOMER_ORDER_RELATIONSHIP)->get()[0];
                $code = HttpResponseCode::OK;
            }
            else {
                $message = $validator->errors();
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else {
            $message = ['message' => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Mark an order as delivered by ID and validation code
     * The rider must be logged in
     */
    public function close(Request $request, $orderId) {

    }

    /**
     * Get details for a given order from customer side
     */
    public function readAsCustomer($orderId) {
        $customer = Auth::guard("customer")->user();

        if(isset($customer)) {
            $order = $customer->orders()->with(OrderController::CUSTOMER_ORDER_RELATIONSHIP)->where('id', $orderId)->get();
            if(isset($order[0])) {
                $message = $order[0];
                $code = HttpResponseCode::OK;
            }
            else {
                $message = ['message' => 'Order not found'];
                $code = HttpResponseCode::NOT_FOUND;
            }
        }
        else {
            $message = ['message' => 'Unauthorized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Get current logged in customer's order list
     */
    public function readCustomerOrders() {
        $customer = Auth::guard("customer")->user();

        if(isset($customer)) {
            $message = $customer->orders()->with(OrderController::CUSTOMER_ORDER_RELATIONSHIP)->orderBy('estimated_delivery_time', 'desc')->get();
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ['message' => 'Unauthorized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Get details for a given order from restaurateur side
     */
    public function readAsRestaurateur($orderId) {
        $restaurateur = Auth::guard("restaurateur")->user();

        if(isset($restaurateur)) {
            $order = $restaurateur->orders()->with(OrderController::RESTAURATEUR_ORDER_RELATIONSHIP)->where('id', $orderId)->get();
            if(isset($order[0])) {
                $message = $order[0];
                $code = HttpResponseCode::OK;
            }
            else {
                $message = ['message' => 'Order not found'];
                $code = HttpResponseCode::NOT_FOUND;
            }
        }
        else {
            $message = ['message' => 'Unauthorized'];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     *  Get the list of delivered orders for the current logged in restaurateur
     * The restaurateur must be logged in
     */
    public function readRestaurateurDeliveredOrders() {
        $restaurateur = Auth::guard("restaurateur")->user();

        if(isset($restaurateur)) {
            $message = $restaurateur->deliveredOrders()->with(OrderController::RESTAURATEUR_ORDER_RELATIONSHIP)->orderBy('actual_delivery_time', 'desc')->get();
            $code = HttpResponseCode::OK;
        }
        else{
            $message = ['message' => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

     /**
     * Get the list of in progress orders for the current logged in restaurateur
      * The restaurateur must be logged in
     */
    public function readRestaurateurPendingOrders() {
        $restaurateur = Auth::guard("restaurateur")->user();

        if(isset($restaurateur)) {
            $message = $restaurateur->pendingOrders()->with(OrderController::RESTAURATEUR_ORDER_RELATIONSHIP)->orderBy('estimated_delivery_time', 'asc')->get();
            $code = HttpResponseCode::OK;
        }
        else{
            $message = ['message' => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
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

                    if($start < $now) {
                        $start = clone $now;
                        $startMinutes = (int) $start->format('i');
                        $startHour = (int) $start->format('H');
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
