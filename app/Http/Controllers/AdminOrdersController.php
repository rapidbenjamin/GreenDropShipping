<?php

namespace App\Http\Controllers;

use App\Libraries\Magento\MagentoApi;
use Illuminate\Http\Request;
use App\Order;
use App\OrderShippingAddress;
use App\Status;
use App\OrderDetails;
use App\PaymentSession;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminOrdersController extends Controller
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

    public function index(Request $request)
    {
        $this->authorize('view-admin-orders');

        $order_list = Order::select(
            'orders.*',
            'osa.first_name',
            'osa.last_name',
            'st1.name as status1',
            'st1.color as color1',
            'st2.name as status2',
            'st2.color as color2',
            'us.name as merchant_name'
        )
            ->join('order_shipping_address as osa', 'orders.id', 'osa.id_order')
            ->join('status as st1', 'st1.id', 'orders.financial_status')
            ->join('status as st2', 'st2.id', 'orders.fulfillment_status')
            ->join('users as us', 'us.id', 'orders.id_customer');

        if ($request->idOrder != '' && $request->idOrder > 0) {
            $order_list = $order_list->where('magento_order_id', $request->idOrder);
        }

        if ($request->dateFrom != '' && $request->dateTo != '') {
            $order_list = $order_list->whereDate('orders.created_at', '>=', $request->dateFrom)
                ->whereDate('orders.created_at', '<=', $request->dateTo);
        }

        if ($request->selectFS > 0) {
            $order_list = $order_list->where('orders.financial_status', $request->selectFS);
        }

        if ($request->selectOS > 0) {
            $order_list = $order_list->where('orders.fulfillment_status', $request->selectOS);
        }

        if ($request->merchant != '') {
            $order_list = $order_list->where('us.name', 'like', '%' . $request->merchant . '%');
        }

        if ($request->merchantid != '') {
            $order_list = $order_list->where('orders.id_customer', $request->merchantid);
        }

        return view('admin_orders', array(
            'order_list' => $order_list->orderBy('orders.updated_at', 'desc')->take(10)->get(),
            'status' => Status::get(),
            'total_count' => $order_list->count()
        ));
    }

    public function show(Order $orders)
    {
        $osa = OrderShippingAddress::select('order_shipping_address.*')
            ->where('order_shipping_address.id_order', $orders->id)
            ->first();

        $fs = Status::select('status.*')
            ->where('status.id', $orders->financial_status)
            ->first();

        $os = Status::select('status.*')
            ->where('status.id', $orders->fulfillment_status)
            ->first();

        $merchant = User::find($orders->id_customer);

        $order_products = OrderDetails::select(
            'order_details.sku',
            'order_details.price',
            'order_details.quantity',
            'products.name',
            'products.images',
            'my_products.profit'
        )
            ->join('products', 'order_details.sku', 'products.sku')
            ->join('import_list', 'import_list.id_product', 'products.id')
            ->join('my_products', 'import_list.id', 'my_products.id_imp_product')
            ->where('import_list.id_customer', $orders->id_customer)
            ->whereNull('my_products.deleted_at')
            ->where('order_details.id_order', $orders->id)->get();

        foreach ($order_products as $pro) {
            if ($pro['images'] != null && count(json_decode($pro['images'])) > 0)
                $pro->image_url = env('URL_MAGENTO_IMAGES') . '/dc09e1c71e492175f875827bcbf6a37c' . json_decode($pro->images)[0]->file;
            else
                $pro->image_url = env('URL_MAGENTO_IMAGES') . '/dc09e1c71e492175f875827bcbf6a37cno_selection';
        }

        $sessionPay = PaymentSession::where('id_orders', 'like', "%$orders->id%")
            ->whereDate('created_at', '>=', $orders->created_at)->orderBy('id', 'desc')->first();

        $user_canceled = User::find($orders->user_id_canceled);
        $user_canceled_name = '';
        $api = MagentoApi::getInstance();
        $criteria = [
            'searchCriteria[filterGroups][1][filters][0][field]' => 'increment_id',
            'searchCriteria[filterGroups][1][filters][0][value]' => $orders->magento_order_id,
            'searchCriteria[filterGroups][1][filters][0][condition_type]' => "eq"
        ];
        $mg_order = $api->query('GET', 'orders', $criteria);

        if ($user_canceled != null) {
            $user_canceled_name = $user_canceled->name;
        }

        return view('admin_orders_detail', array(
            'order' => $orders,
            'mg_order' => json_decode($mg_order)->items[0],
            'osa' => $osa,
            'fs' => $fs,
            'os' => $os,
            'payment_intent' => !is_null($sessionPay) ? $sessionPay->payment_intent : '',
            'payment_card_number' => !is_null($sessionPay) ? $sessionPay->card_last4 : '',
            'order_products' => $order_products,
            'merchant' => $merchant,
            'user_canceled' => $user_canceled_name
        ));
    }

    public function cancel(Order $order)
    {
        $order->fulfillment_status = 9; //canceled
        $order->user_id_canceled = Auth::user()->id;
        $order->canceled_at = date('Y-m-d H:i:s');
        $order->save();

        return redirect('admin/orders');
    }

    public function exportCSV(Request $request)
    {
        $order_list = Order::select(
            'orders.id',
            'orders.order_number_shopify',
            'orders.total_shopify',
            'osa.first_name',
            'osa.last_name',
            'p.name',
            'st1.name as financial_state',
            'st2.name as order_state',
            'od.price',
            'od.quantity'
        )
            ->join('order_shipping_address as osa', 'orders.id', 'osa.id_order')
            ->join('status as st1', 'st1.id', 'orders.financial_status')
            ->join('order_details as od', 'orders.id', 'od.id_order')
            ->join('products as p', 'p.sku', 'od.sku')
            ->join('status as st2', 'st2.id', 'orders.fulfillment_status')
            ->whereIn('orders.id', json_decode($request->ids))->orderBy('orders.id')->get()->toArray();



        $now = gmdate("D, d M Y H:i:s");

        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename=orders.csv");
        header("Content-Transfer-Encoding: binary");

        ob_start();
        $df = fopen("php://output", 'w');

        fputcsv($df, array_keys(reset($order_list)));
        foreach ($order_list as $merchant) {
            fputcsv($df, $merchant);
        }

        fclose($df);
        echo ob_get_clean();
        die();
    }
}
