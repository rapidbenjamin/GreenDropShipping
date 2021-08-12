<?php

namespace App\Http\Controllers;

use App\Libraries\OrderStatus;
use App\MonthlyRecurringPlan;
use App\Order;
use App\PaymentSession;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function createCheckoutSession(Request $request)
    {

        $orders = Order::whereIn('id', $request->orders)->where('financial_status', OrderStatus::Outstanding)->get();

        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
        $line_items = array();
        if (!count($orders)) {
            return response()->json(['message' => 'you have to select valid orders'], 500);
        }

        $description = "";
        foreach ($orders as $order) {
            if ($order != null) {
                $user = User::find($order->id_customer);
                $line_items[] = [
                    'name' => 'Order ' . $order->order_number_shopify,
                    'amount' => ($order->total + $order->shipping_price) * 100,
                    'quantity' => 1,
                    'currency' => 'usd',
                    'description' => "Order {$order->order_number_shopify} - Store Id: {$user->id} {$user->shopify_url}"
                ];
                $description .= "Order {$order->order_number_shopify} - Store Id: {$user->id} {$user->shopify_url}\n";
            }
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $line_items,
            'mode' => 'payment',
            'customer_email' => $user->email,
            'client_reference_id' => $user->id,
            'success_url' => env('APP_URL') . '/orders?payment=success',
            'cancel_url' => env('APP_URL') . '/orders?payment=cancel',
            'payment_intent_data' => [
                'description' => $description
            ]
        ]);

        if (isset($session->id)) {
            DB::beginTransaction();
            $paymentSession = new PaymentSession();
            $paymentSession->id_session = $session->id;
            $paymentSession->payment_intent = $session->payment_intent;
            $paymentSession->id_orders = implode(",", $orders->pluck('id')->toArray());
            $paymentSession->status = 'In Process';
            $paymentSession->data = json_encode($session);
            $paymentSession->save();
            DB::commit();
            return response()->json(['id' => $session->id]);
        } else
            return response()->json(['message' => 'fail to create session'], 500);
    }
}
