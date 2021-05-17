<?php



namespace App\Http\Controllers;



use App\Libraries\Magento\MOrder;

use App\Libraries\OrderStatus;

use App\Order;

use App\PaymentSession;

use Exception;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;



class StripeWebHooksController extends Controller

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



    public function checkPaymentSuccess(Request $request)

    {

        Log::info($request->all());

        $payment_session = PaymentSession::where('id_session', $request->data['object']['id'])->first();

        if ($payment_session != null) {

            DB::beginTransaction();

            $payment_session->status = 'Success';

            $payment_session->save();



            $orders = Order::whereIn('id', explode(',', $payment_session->id_orders))->get();

            foreach ($orders as $order) {

                $order->financial_status = OrderStatus::Paid;

                $order->fulfillment_status = OrderStatus::InProcessOrder;

                $order->save();

            }

            DB::commit();



            foreach ($orders as $order) {

               $magento_order = MOrder::createOrderv2($order);
               Log::info("CUSTOMPAULO-MAGENTOORDER");
               Log::info($magento_order);

               $order->magento_quote_id = $magento_order['quote_id'].'';

               $order->magento_order_id = $magento_order['reserved_order_id'];

               $order->save();

            }



            $response = $this->request('GET', 'payment_intents/', $payment_session->payment_intent);

            if ($response['HTTP_CODE'] == 200) {

                $card = $response['body']['charges']['data'][0]['payment_method_details']['card'];

                $payment_session->card_brand = $card['brand'];

                $payment_session->card_exp_month = $card['exp_month'];

                $payment_session->card_exp_year = $card['exp_year'];

                $payment_session->card_last4 = $card['last4'];

                $payment_session->save();

            }

        }

        return response()->json(array('result' => 'ok'));

    }



    public function checkPaymentFail(Request $request)

    {

        Log::info($request->all());

        $payment_session = PaymentSession::where('id_session', $request->data['object']['id'])->first();

        if ($payment_session != null) {

            $payment_session->status = 'Fail';

            $payment_session->save();

        }



        return response()->json(array('result' => 'ok'));

    }



    public function test($payment_intent)

    {

        $response = $this->request('GET', 'payment_intents/', $payment_intent);

        if ($response['HTTP_CODE'] == 200) {

            $card = $response['body']['charges']['data'][0]['payment_method_details']['card'];

            $brand = $card['brand'];

            $exp_month = $card['exp_month'];

            $exp_year = $card['exp_year'];

            $last4 = $card['last4'];

        }

    }



    public function request($method, $url, $payment_intent, $data = null)

    {

        $ch = curl_init(env('URL_STRIPE_API') . $url . $payment_intent);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HEADER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . env('STRIPE_SECRET_KEY'), 'Content-Length: ' . strlen($data)));

        $result = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $header = substr($result, 0, $header_size);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $body = substr($result, $header_size);

        curl_close($ch);

        $body = json_decode($body, true);



        foreach (explode("\n", $header) as $part) {

            $middle = explode(":", $part, 2);

            if (count($middle) < 2) continue;

            $header_line = trim($middle[0]);

        }

        return array('HTTP_CODE' => $httpcode, 'body' => $body);

    }

}

