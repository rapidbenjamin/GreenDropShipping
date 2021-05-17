<?php

namespace App\Http\Controllers;

use App\Libraries\OrderStatus;
use App\MonthlyRecurringPlan;
use App\Order;
use App\PaymentSession;
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
        //dd(explode("|",$request->shipping));
        //dd($request->shipping());
        //env('URL_MAGENTO')
        //$shipping = explode("|", $request->shipping);
        //$carrier = $shipping[0];
        //$method = $shipping[1];
        //$amount = $shipping[2];
        //$title = $shipping[3];
        //dd($carrier,$method);
        $orders = Order::whereIn('id', $request->orders)->where('financial_status', OrderStatus::Outstanding)->get();
        /*
        $current_period = MonthlyRecurringPlan::where('current', 1)->where('merchant_id', Auth::user()->id)->first();
        if (Auth::user()->plan == 'basic' && $current_period != null) {
            $total_period_orders = $current_period->orders;

            //dd(($total_period_orders + count($orders)) , env('LIMIT_ORDERS', 1));
            if (($total_period_orders + count($orders)) > env('LIMIT_ORDERS', 1)) {
                return response()->json(['message' => 'Order limit is reached'], 406);
            }
        }*/


        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
        $line_items = array();
        if (!count($orders)) {
            return response()->json(['message' => 'yo have to select valid orders'], 500);
        }

        foreach ($orders as $order) {
            if ($order != null) {
                //$order->shipping_method_code = $carrier;
                //$order->shipping_carrier_code = $method;
                //$order->shipping_price = $amount;
                //$order->shipping_title = $title;
                //$order->save();
                $line_items[] = [
                    'name' => 'Order ' . $order->order_number_shopify,
                    'amount' => ($order->total + $order->shipping_price) * 100,
                    'quantity' => 1,
                    'currency' => 'usd',
                ];
            }
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $line_items,
            'mode' => 'payment',
            'success_url' => env('APP_URL') . '/orders?payment=success',
            'cancel_url' => env('APP_URL') . '/orders?payment=cancel',
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
