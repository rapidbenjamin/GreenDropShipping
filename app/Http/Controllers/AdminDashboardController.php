<?php

namespace App\Http\Controllers;

use App\Libraries\OrderStatus;
use App\Order;
use App\Products;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;

class AdminDashboardController extends Controller
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

    public function index()
    {
        $this->authorize('view-admin-dashboard');

        return view('admin_dashboard', []);
    }

    public function logs()
    {
        $this->authorize('view-admin-dashboard');
        $logs = Storage::disk('local')->files('gds');

        $lastLog = [];
        $logs_current = [];

        if (count($logs)) {
            $lastLog = $logs[count($logs) - 1];
            $contents = Storage::get($lastLog);
            $rows = explode(PHP_EOL, $contents);

            foreach ($rows as $row) {
                $row = explode('|', $row);

                $loge = new stdClass();
                $loge->date = $row[0];
                $loge->merchant = $row[1];
                $loge->action = $row[2];
                $loge->message = $row[3];

                $logs_current[] = $loge;
            }
        }

        //SPLIT
        $split = 6; //six month

        if (count($logs) > $split) { //delete after
            $lim = count($logs) - $split;

            for ($i = 0; $i < $lim; $i++) {
                Storage::delete($logs[$i]);
            }
        }

        return view('admin_logs', [
            'logs' => $logs,
            'lastLog' => $logs_current
        ]);
    }

    public function downloadLog(Request $request)
    {
        $file = storage_path('app/' . $request->log);
        $filetype = filetype($file);
        $filename = basename($file);
        header("Content-Type: " . $filetype);
        header("Content-Length: " . filesize($file));
        header("Content-Disposition: attachment; filename=" . $filename);
        readfile($file);
    }

    public function getData(Request $request)
    {
        return response()->json([
            'sales' => DB::table('orders')
                ->where('financial_status', OrderStatus::Paid)
                ->whereDate('created_at', '>=', $request->from)
                ->whereDate('created_at', '<=', $request->to)
                ->select(array(DB::Raw('ROUND(sum(total),2) as Total'), DB::Raw('DATE(created_at) as date_at')))
                ->groupBy('date_at')->get(),
            'num_sales' => Order::where('financial_status', OrderStatus::Paid)
                ->whereDate('created_at', '>=', $request->from)
                ->whereDate('created_at', '<=', $request->to)->count(),
            'num_returns' => Order::where('fulfillment_status', OrderStatus::Returned)
                ->whereDate('created_at', '>=', $request->from)
                ->whereDate('created_at', '<=', $request->to)->count(),
            'total_sales' => Order::select(array(DB::Raw('ROUND(sum(total),2) as TotalSales')))
                ->whereDate('created_at', '>=', $request->from)
                ->whereDate('created_at', '<=', $request->to)->get(),
            'new_installations' => User::whereNotNull('shopify_url')
                ->whereDate('created_at', '>=', $request->from)
                ->whereDate('created_at', '<=', $request->to)->count(),
            'orders' => DB::table('orders')
                ->where('financial_status', OrderStatus::Paid)
                ->whereDate('created_at', '>=', $request->from)
                ->whereDate('created_at', '<=', $request->to)
                ->select(array(DB::Raw('count(*) as Total'), DB::Raw('DATE(created_at) as date_at')))
                ->groupBy('date_at')->get(),
            'returns' => DB::table('orders')
                ->where('financial_status', OrderStatus::Returned)
                ->whereDate('created_at', '>=', $request->from)
                ->whereDate('created_at', '<=', $request->to)
                ->select(array(DB::Raw('count(*) as Total'), DB::Raw('DATE(created_at) as date_at')))
                ->groupBy('date_at')->get(),
            'newinstalls' => DB::table('users')
                ->whereNotNull('shopify_url')
                ->whereDate('created_at', '>=', $request->from)
                ->whereDate('created_at', '<=', $request->to)
                ->select(array(DB::Raw('count(*) as Total'), DB::Raw('DATE(created_at) as date_at')))
                ->groupBy('date_at')->get(),
            'lastorders' => User::whereDate('orders.created_at', '>=', $request->from)
                ->whereDate('orders.created_at', '<=', $request->to)
                ->select(array(
                    DB::Raw('(SELECT count(*) FROM order_details WHERE order_details.id_order = orders.id) as Products'), 'users.name', 'shopify_url', 'orders.total', 'orders.created_at',
                    DB::Raw('(select status.name from status WHERE orders.financial_status = status.id ) as status'),
                    DB::Raw('(select status.color from status WHERE orders.financial_status = status.id ) as statuscolor')
                ))
                ->join('orders', 'users.id', 'orders.id_customer')
                ->orderBy('orders.created_at', 'desc')
                ->limit(10)->get(),
            'topmerchants' => User::whereDate('orders.created_at', '>=', $request->from)
                ->whereDate('orders.created_at', '<=', $request->to)
                ->select(array(DB::Raw('count(orders.id) as num_orders'), 'users.name',DB::Raw('ROUND(sum(total),2) as total')))
                ->join('orders', 'users.id', 'orders.id_customer')
                ->groupBy('users.name')
                ->orderBy('num_orders', 'desc')
                ->limit(10)->get(),
            'bestsellers' => Products::select(array('products.name', 'products.sku', DB::Raw('count(*) as Counts'), DB::Raw('ROUND(sum(order_details.price),2) as total')))
                ->join('order_details', 'products.sku', 'order_details.sku')
                ->join('orders', 'orders.id', 'order_details.id_order')
                ->groupBy('products.sku', 'products.name')
                ->orderBy('Counts', 'desc')
                ->limit(10)->get(),
            'plans_basic' =>  DB::table('users')
                ->whereNotNull('shopify_url')
                ->where('plan', 'basic')
                ->whereDate('created_at', '>=', $request->from)
                ->whereDate('created_at', '<=', $request->to)
                ->select(array(DB::Raw('count(*) as Numero'), DB::Raw('DATE(created_at) as date_at')))
                ->groupBy('date_at')->get(),
            'plans_free' =>  DB::table('users')
                ->whereNotNull('shopify_url')
                ->where('plan', 'free')
                ->whereDate('created_at', '>=', $request->from)
                ->whereDate('created_at', '<=', $request->to)
                ->select(array(DB::Raw('count(*) as Numero'), DB::Raw('DATE(created_at) as date_at')))
                ->groupBy('date_at')->get(),
            'plans_advance' =>  DB::table('users')
                ->whereNotNull('shopify_url')
                ->where('plan', 'advance')
                ->whereDate('created_at', '>=', $request->from)
                ->whereDate('created_at', '<=', $request->to)
                ->select(array(DB::Raw('count(*) as Numero'), DB::Raw('DATE(created_at) as date_at')))
                ->groupBy('date_at')->get()
        ]);
    }
}
