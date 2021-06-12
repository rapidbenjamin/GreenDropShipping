<?php

namespace App\Http\Controllers;

use App\Libraries\Magento\MOrder;
use App\Libraries\OrderStatus;
use App\Log;
use App\MonthlyRecurringPlan;
use App\MonthlyRecurringPlanOrders;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Order;
use App\OrderShippingAddress;
use App\Status;
use App\OrderDetails;
use App\PaymentSession;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;

class OrdersController extends Controller
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
		$this->authorize('plan_view-manage-orders');
		$order_list = Order::select(
			'orders.*',
			'osa.first_name',
			'osa.last_name',
			'st1.name as status1',
			'st1.color as color1',
			'st2.name as status2',
			'st2.color as color2',
			'st1.id as financial_status',
			'st2.id as fulfillment_status'
		)
			->join('order_shipping_address as osa', 'orders.id', 'osa.id_order')
			->join('status as st1', 'st1.id', 'orders.financial_status')
			->join('status as st2', 'st2.id', 'orders.fulfillment_status')
			->where('orders.id_customer', Auth::user()->id);

		$searchBy = 'Search By';
		if ($request->order != '' && $request->order > 0) {
			$order_list = $order_list->where('order_number_shopify', '#' . $request->order);
			$searchBy .= ' order number => ' . $request->order;
		} else {
			if ($request->from != '' && $request->to != '') {
				$searchBy .= ' dates => ' . $request->from  . '-' . $request->to;
				$order_list = $order_list->whereDate('created_at', '>=', $request->from)
					->whereDate('created_at', '<=', $request->to);
			}
		}

		$current_period = MonthlyRecurringPlan::where('current', 1)->where('merchant_id', Auth::user()->id)->first();
		$total_period_orders = 0;
		$basic_period_orders = '';
		if (Auth::user()->plan == 'basic' && $current_period != null) {
			$period_orders = MonthlyRecurringPlanOrders::where('period_id', $current_period->id)->Join('orders', 'orders.id', '=', 'monthly_recurring_plan_orders.order_id')->get();
			$total_period_orders = $period_orders->filter(function ($value, $key) {
				return $value->role != 'admin';
			})->count();
			$basic_period_orders = $current_period->start_date . ' - ' . $current_period->end_date;
		}

		$notifications = Order::where('financial_status', OrderStatus::Outstanding)->where('fulfillment_status', OrderStatus::NewOrder)->where('orders.id_customer', Auth::user()->id)->count();
		if ($request->notifications != '' && $request->notifications) {
			$searchBy .= ' pending payments';
			$order_list = $order_list->where('financial_status', OrderStatus::Outstanding)->where('fulfillment_status', OrderStatus::NewOrder);
		}
		if ($request->st1 > 0) {
			$searchBy .= ' financial state => ' . $request->st1;
			$order_list = $order_list->where('orders.financial_status', $request->st1);
		}

		if ($request->st2 > 0) {
			$searchBy .= ' order state => ' . $request->st2;
			$order_list = $order_list->where('orders.fulfillment_status', $request->st2);
		}


		self::GDSLOG('Search Orders', ($searchBy == 'Search By' ? 'View Orders' : $searchBy));

		return view('orders', array(
			'order_list' => $order_list->orderBy('orders.id_shopify', 'desc')->paginate(50),
			'from' => $request->from,
			'to' => $request->to,
			'order' => $request->order,
			'total_period_orders' => $total_period_orders,
			'basic_period_orders' => $basic_period_orders,
			'order_limit' => env('LIMIT_ORDERS', 1),
			'notifications' => $notifications,
			'status' => Status::get()
		));
	}

	public function show(Order $orders)
	{
		$this->authorize('plan_view-manage-orders');
	    if(Auth::user()->id == $orders->id_customer){
		$osa = OrderShippingAddress::select('order_shipping_address.*')
			->where('order_shipping_address.id_order', $orders->id)
			->first();

		$fs = Status::select('status.*')
			->where('status.id', $orders->financial_status)
			->first();

		$os = Status::select('status.*')
			->where('status.id', $orders->fulfillment_status)
			->first();


		$order_products = OrderDetails::select(
			'order_details.sku',
			'order_details.price',
			'order_details.quantity',
			'products.name',
			'products.images'
		)->join('products', 'order_details.sku', 'products.sku')
            ->where('order_details.id_order', $orders->id)->get();

		foreach ($order_products as $pro) {
			$pro->image_url = env('URL_MAGENTO_IMAGES'). '/dc09e1c71e492175f875827bcbf6a37c' . json_decode($pro->images)[0]->file;
		}
		$sessionPay = PaymentSession::where('id_orders', 'like', "%$orders->id%")
			->whereDate('created_at', '>=', $orders->created_at)->orderBy('id', 'desc')->first();

		$user_canceled = User::find($orders->user_id_canceled);
		$user_canceled_name = '';
		if ($user_canceled != null) {
			$user_canceled_name = $user_canceled->name;
		}

		self::GDSLOG('View Order Detail', 'View Order Detail => ' . $orders->id);

		return view('order_detail', array(
			'order' => $orders,
			'osa' => $osa,
			'fs' => $fs,
			'os' => $os,
			'payment_intent' => !is_null($sessionPay) ? $sessionPay->payment_intent : '',
			'payment_card_number' => !is_null($sessionPay) ? $sessionPay->card_last4 : '',
			'order_products' => $order_products,
			'isValidOrder' => self::isValidOrderLimit($orders->id),
			'user_canceled' => $user_canceled_name,
			'states' => MOrder::USAstates(),
			'state_key' => MOrder::getSateIdByName($osa->province)
		));
	    }else{
	        return redirect('orders');
	    }
	}


    //deprecated
	public static function isValidOrderLimit($order_id)
	{

		if (Auth::user()->plan == 'basic') {
			$order = Order::find($order_id);
			$current_period = MonthlyRecurringPlan::where('current', 1)->where('merchant_id', Auth::user()->id)->first();
			if ($current_period != null) {
				if (date('Y-m-d', strtotime($order->created_at)) <= date($current_period->start_date)) {
					return true;
				}
				//takes into account the orders which are within the LIMIT_ORDERS to validate
				$current_period_orders = MonthlyRecurringPlanOrders::where('period_id', $current_period->id)->orderBy('order_id', 'asc')
					->take(env('LIMIT_ORDERS', 1))->get();
				$found = $current_period_orders->first(function ($value, $key) use ($order_id) {
					return $value->order_id == $order_id;
				});
				return $found != null;
			}
			return false;
		}
		return true;
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
			'od.price',
			'od.quantity',
			DB::raw('(od.price * od.quantity) as total', 'st1.name as financial_state', 'st2.name as order_state')
		)
			->join('order_shipping_address as osa', 'orders.id', 'osa.id_order')
			->join('status as st1', 'st1.id', 'orders.financial_status')
			->join('order_details as od', 'orders.id', 'od.id_order')
			->join('products as p', 'p.sku', 'od.sku')
			->join('status as st2', 'st2.id', 'orders.fulfillment_status')->whereIn('orders.id', explode(',', $request->orders))
			->where('orders.id_customer', Auth::user()->id)->get()->toArray();

		self::GDSLOG('Export Order', 'Export Order => ' . $request->orders);

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

		fwrite($df, implode(";", array_keys(reset($order_list))) . "\n");
		foreach ($order_list as $merchant) {
			fwrite($df, implode(";", $merchant) . "\n");
		}
		fclose($df);
		echo ob_get_clean();
		die();
	}
	//deprecated
	public function createCart(Request $request)
	{
		$order = Order::find($request->order_id);
		$cart_id = MOrder::createCart(json_decode($order->data), $order);
		$result = MOrder::getEstimatesShipping(json_decode($order->data), $cart_id);

		return response()->json(collect(json_decode($result))->sortBy('amount')->toArray());
	}

	public function saveAddress(Request $request)
	{
		$order = OrderShippingAddress::where('id_order', $request->order_id)->first();
		if ($order != null) {

			self::GDSLOG('Update Address', 'Update Address => ' . $request->order_id);

			//Current address
			self::GDSLOG('Current Address', '------');
			self::GDSLOG('address1', '=>' . $order->address1);
			self::GDSLOG('address2', '=>' . $order->address2);
			self::GDSLOG('city', '=>' . $order->city);
			self::GDSLOG('province', '=>' . $order->province);
			self::GDSLOG('province_code', '=>' . $order->province_code);
			self::GDSLOG('zip', '=>' . $order->zip);

			$order->address1 = $request->address1;
			$order->address2 = $request->address2;
			$order->city = $request->city;
			$order->province = MOrder::getSateById($request->state);
			$order->province_code = MOrder::getSateCodeByName($order->province);
			$order->zip = $request->zip;
			$order->update_merchant_id = Auth::user()->id;
			$order->update_date = date('Y-m-d H:i:s');
			$order->save();

			//update address
			self::GDSLOG('New Address', '------');
			self::GDSLOG('address1', '=>' . $order->address1);
			self::GDSLOG('address2', '=>' . $order->address2);
			self::GDSLOG('city', '=>' . $order->city);
			self::GDSLOG('province', '=>' . $order->province);
			self::GDSLOG('province_code', '=>' . $order->province_code);
			self::GDSLOG('zip', '=>' . $order->zip);
		}

		return redirect('orders/' . $request->order_id);
	}

	public function cancelRequest(Order $order)
	{

		$res = MOrder::changeStatus($order->magento_quote_id);
		if ($res) {
			$order->fulfillment_status = 11; //canceled Request
			$order->user_id_canceled = Auth::user()->id;
			$order->canceled_at = date('Y-m-d H:i:s');
			$order->save();
			$this->updateLimitOrderWhenCanceling($order);
			self::GDSLOG('Cancel Order', 'Cancel Request => ' .  $order->id);
		}
		return redirect('orders/');
	}

	public function cancel(Order $order)
	{
		$order->fulfillment_status = 9; //canceled
		$order->user_id_canceled = Auth::user()->id;
		$order->canceled_at = date('Y-m-d H:i:s');
		$order->save();
		self::GDSLOG('Cancel Order', 'Cancel Order => ' . $order->id);
		$this->updateLimitOrderWhenCanceling($order);
		return redirect('orders/');
	}

	public static function GDSLOG($action, $message)
	{
		$log = date('Y-m-d H:i:s') . '| Merchant ' . Auth::user()->id . '| Shop ' . Auth::user()->name . ' | ' .  $action . ' | ' . $message;
		Storage::disk('local')->append("gds/" . date('Y-m') . '.txt', $log);
	}

	//Update within the current period
	public function updateLimitOrderWhenCanceling($order)
	{
		$current_period = MonthlyRecurringPlan::where('current', 1)->where('merchant_id', Auth::user()->id)->first();
		if ($current_period != null) {
			$current_period_orders = MonthlyRecurringPlanOrders::where('period_id', $current_period->id)->orderBy('order_id', 'asc')
				->take(env('LIMIT_ORDERS', 1))->get();
			$found = $current_period_orders->first(function ($value, $key) use ($order) {
				return $value->order_id == $order->id;
			});
			if ($found != null) {
				$found->delete();
			}
		}
	}

	public function isOutOfLimit($merchantId)
	{
		$current_period = MonthlyRecurringPlan::where('current', 1)->where('merchant_id', $merchantId)->first();
		if ($current_period != null) {
			$count = MonthlyRecurringPlanOrders::where('period_id', $current_period->id)
				->orderBy('order_id', 'asc')->count();
			if ($count >= env('LIMIT_ORDERS', 1))
				return true;
		}
		return false;
	}
}
