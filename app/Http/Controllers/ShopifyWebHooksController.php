<?php

namespace App\Http\Controllers;

use App\Libraries\Magento\MOrder;
use App\Libraries\Shipping;
use App\MonthlyRecurringPlan;
use App\MonthlyRecurringPlanOrders;
use App\MyProducts;
use App\Order;
use App\OrderDetails;
use App\OrderShippingAddress;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShopifyWebHooksController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    // Mandatorie GDPR webhook
    public function customerDataRequest(Request $request)
    {
        //Request to view stored customer data (customers/data_request)



        return response()->json(['result' => true]);
    }

    // Mandatorie GDPR webhook
    public function customerDataErasure(Request $request)
    {


        // 1. Request deletion of customer data (customers/redact)
        $shop_url = $request->shop_domain;

        // 2. customer email
        $customer_email = $request->customer->email;

        // 3. Orders to delete
        $orders_to_redact = $request->orders_to_redact;

        // 4. delete customer information from order_shipping_address and order details and orders
        foreach ($orders_to_redact as $key => $otr) {
            $id_order = Order::select('orders.id')->where('id_shopify', $otr)->first();

            // A. delete from order shipping address
            $res1 = OrderShippingAddress::where('id_order', $id_order['id'])->delete();

            // B. delete from order detail
            $res2 = OrderDetails::where('id_order', $id_order['id'])->delete();

            // C. delete from orders
            $res3 = Order::where('orders.id', $id_order['id'])->delete();
        }


        return response()->json(['result' => true]);
    }


    // Mandatorie GDPR webhook
    public function shopDataErasure(Request $request)
    {
        // 1. Request deletion of shop data (shop/redact)
        $shop_url = $request->shop_domain;

        // 2. delete user (shop)
        $res1 = User::where('users.shopify_url', $shop_url)->delete();


        return response()->json(['result' => true]);
    }

    public function createOrder(Request $request)
    {

        //Validation order do not exist
        $res = Order::select('orders.id')->where("id_shopify", $request->id)->first();

        if (!$res['id'] > 0) {
            $order_details_list = [];
            $total_shopify = 0;
            $total_green = 0;
            $id_customer = 0;

            try {
                foreach ($request->line_items as $key => $line) {
                    $myproducts =  MyProducts::where('id_shopify', $line['product_id'])->first();

                    if ($myproducts != null && (int)$line['quantity'] <= $myproducts->stock) {
                        $id_customer = $myproducts->id_customer;

                        if ($myproducts->import_list != null && $myproducts->import_list->product != null) {
                            $product_magento = $myproducts->import_list->product;
                            $order_details = new OrderDetails();
                            $order_details->id_order = null;
                            $order_details->id_variant = $line['variant_id'];
                            $order_details->id_product = $line['product_id'];
                            $order_details->sku = $line['sku'];
                            $order_details->quantity = (int)$line['quantity'];
                            $order_details->price_shopify = (float)$line['price'];
                            $order_details->price = (float)$product_magento->price;
                            $order_details_list[] = $order_details;
                            //set totals
                            $total_shopify += ($order_details->price_shopify * $order_details->quantity);
                            $total_green += ($order_details->price * $order_details->quantity);
                        }
                    } elseif ($myproducts != null && (int)$line['quantity'] > $myproducts->stock) {
                        $id_customer = $myproducts->id_customer;

                        if ($myproducts->import_list != null && $myproducts->import_list->product != null) {
                            $product_magento = $myproducts->import_list->product;
                            $order_details = new OrderDetails();
                            $order_details->id_order = null;
                            $order_details->id_variant = $line['variant_id'];
                            $order_details->id_product = $line['product_id'];
                            $order_details->sku = $line['sku'];
                            $order_details->quantity = (int)$line['quantity'];
                            $order_details->price_shopify = (float)$line['price'];
                            $order_details->price = (float)$product_magento->price;
                            $order_details_list2[] = $order_details;
                            //set totals
                            $total_shopify += ($order_details->price_shopify * $order_details->quantity);
                            $total_green += ($order_details->price * $order_details->quantity);
                        }
                    }
                }

                //Validate Email
                if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                    $user = User::where('id', $id_customer)->first();
                    $request->email = $user->email;
                }

                if (count($order_details_list) > 0 && $id_customer > 0) {
                    DB::beginTransaction();
                    $order = new Order();
                    $order->id_shopify = $request->id;
                    $order->order_number_shopify = $request->name;
                    $order->id_customer = $id_customer;
                    $order->total_shopify = $total_shopify + (float)$request->shipping_lines[0]['price'];;
                    $order->total_weight_shopify = $request->total_weight;
                    $order->financial_status = 1;
                    $order->fulfillment_status = 4;
                    $order->shipping_name = $request->shipping_lines[0]['title'];
                    $order->shipping_price_shopify = (float)$request->shipping_lines[0]['price'];
                    $order->shipping_price = 0;
                    $order->total = $total_green;
                    $order->data = json_encode($request->all());
                    $order->save();
                    //set id_order to items
                    foreach ($order_details_list as $item) {
                        $item->id_order = $order->id;
                        $item->save();
                    }
                    $shipping_address = new OrderShippingAddress();
                    $shipping_address->email = $request->email;
                    $shipping_address->id_order = $order->id;
                    $shipping_address->first_name = $request->shipping_address['first_name'];
                    $shipping_address->last_name = $request->shipping_address['last_name'];
                    $shipping_address->address1 = $request->shipping_address['address1'];

                    if ($request->shipping_address['phone']) {
                        $shipping_address->phone = $request->shipping_address['phone'];
                    } else {
                        $shipping_address->phone = "999999999";
                    }

                    $shipping_address->city = $request->shipping_address['city'];
                    $shipping_address->zip = $request->shipping_address['zip'];
                    $shipping_address->province = $request->shipping_address['province'];
                    $shipping_address->country = $request->shipping_address['country'];
                    $shipping_address->address2 = $request->shipping_address['address2'];
                    $shipping_address->latitude = $request->shipping_address['latitude'];
                    $shipping_address->longitude = $request->shipping_address['longitude'];
                    $shipping_address->country_code = $request->shipping_address['country_code'];
                    $shipping_address->province_code = $request->shipping_address['province_code'];
                    $shipping_address->update_merchant_id = 0;
                    $shipping_address->update_date = date("Y-m-d H:i:s");
                    $shipping_address->save();

                    $current_period = MonthlyRecurringPlan::where('current', 1)->where('merchant_id', $id_customer)->first();
                    if ($current_period != null) {
                        $record = new MonthlyRecurringPlanOrders();
                        $record->period_id = $current_period->id;
                        $record->order_id = $order->id;
                        $record->save();
                    }

                    $orderUtil = new OrdersController();
                    $isOut = $orderUtil->isOutOfLimit($id_customer);
                    if ($isOut) {
                        $order->fulfillment_status = 12;
                        $order->save();
                    }
                    try {
                        //get shipping from magento based on shipping address.
                        $cart_id = MOrder::createCart(json_decode($order->data), $order);
                        $result = MOrder::getEstimatesShipping(json_decode($order->data), $cart_id);
                        $col = collect(json_decode($result))
                            ->where('price_incl_tax', '!=', 0)->sortBy('amount');

                        if (count($col)) { //has at least an item?
                            $met = $col->first(); //take the first shiping const
                            if ($met != null) {
                                $order->shipping_price = $met->price_incl_tax;
                                $order->shipping_method_code = $met->method_code;
                                $order->shipping_carrier_code = $met->carrier_code;
                                $order->shipping_title = $met->method_title;
                                $order->save();
                            }
                        }
                    } catch (Exception $ex) {
                        Log::error('Cannot create cart for magento' . $ex->getMessage());
                    }

                    DB::commit();
                } elseif ($id_customer > 0) {
                    DB::beginTransaction();
                    $order = new Order();
                    $order->id_shopify = $request->id;
                    $order->order_number_shopify = $request->name;
                    $order->id_customer = $id_customer;
                    $order->total_shopify = $total_shopify + (float)$request->shipping_lines[0]['price'];;
                    $order->total_weight_shopify = $request->total_weight;
                    $order->financial_status = 13;
                    $order->fulfillment_status = 9;
                    $order->shipping_name = $request->shipping_lines[0]['title'];
                    $order->shipping_price_shopify = (float)$request->shipping_lines[0]['price'];
                    $order->shipping_price = 0;
                    $order->canceled_at = date('Y-m-d H:i:s');
                    $order->user_id_canceled = $id_customer;
                    $order->notes = 'This order cannot be filled because there is no stock.';
                    $order->total = $total_green;
                    $order->data = json_encode($request->all());
                    $order->save();
                    //set id_order to items
                    foreach ($order_details_list2 as $item) {
                        $item->id_order = $order->id;
                        $item->save();
                    }
                    $shipping_address = new OrderShippingAddress();
                    $shipping_address->email = $request->email;
                    $shipping_address->id_order = $order->id;
                    $shipping_address->first_name = $request->shipping_address['first_name'];
                    $shipping_address->last_name = $request->shipping_address['last_name'];
                    $shipping_address->address1 = $request->shipping_address['address1'];
                    if ($request->shipping_address['phone']) {
                        $shipping_address->phone = $request->shipping_address['phone'];
                    } else {
                        $shipping_address->phone = "999999999";
                    }

                    $shipping_address->city = $request->shipping_address['city'];
                    $shipping_address->zip = $request->shipping_address['zip'];
                    $shipping_address->province = $request->shipping_address['province'];
                    $shipping_address->country = $request->shipping_address['country'];
                    $shipping_address->address2 = $request->shipping_address['address2'];
                    $shipping_address->latitude = $request->shipping_address['latitude'];
                    $shipping_address->longitude = $request->shipping_address['longitude'];
                    $shipping_address->country_code = $request->shipping_address['country_code'];
                    $shipping_address->province_code = $request->shipping_address['province_code'];
                    $shipping_address->update_merchant_id = 0;
                    $shipping_address->update_date = date("Y-m-d H:i:s");
                    $shipping_address->save();

                    $current_period = MonthlyRecurringPlan::where('current', 1)->where('merchant_id', $id_customer)->first();
                    if ($current_period != null) {
                        $record = new MonthlyRecurringPlanOrders();
                        $record->period_id = $current_period->id;
                        $record->order_id = $order->id;
                        $record->save();
                    }


                    $orderUtil = new OrdersController();
                    $isOut = $orderUtil->isOutOfLimit($id_customer);
                    if ($isOut) {
                        $order->fulfillment_status = 12;
                        $order->save();
                    }

                    DB::commit();
                }
            } catch (Exception $ex) {
                Log::error($ex->getMessage());
            }
        } //close main if
    }

    public function runschedule()
    {
        try {
            Artisan::call('schedule:run');
            return Artisan::output();
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }

    public static function GDSLOG($action, $message)
    {
        $log = date('Y-m-d H:i:s') . '| Merchant ' . Auth::user()->id . '| Shop ' . Auth::user()->name . ' | ' .  $action . ' | ' . $message;
        Storage::disk('local')->append("gds/" . date('Y-m') . '.txt', $log);
    }
}
