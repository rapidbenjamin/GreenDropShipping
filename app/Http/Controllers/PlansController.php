<?php

namespace App\Http\Controllers;

use App\Libraries\Shopify\ShopifyAdminApi;
use App\MonthlyRecurringPlan;
use App\User;
use App\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlansController extends Controller
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
        $this->authorize('view-merchant-plans');
        $user = User::find(Auth::user()->id);

        return view('plans', [
            'token' => $user != null ? $user->membership_token : ''
        ]);
    }

    public function saveToken(Request $request)
    {
        $this->authorize('view-merchant-plans');

        //Eval Token
        $tokenStatus = 0;
        $msg = 'Invalid Token';

        if ($request->token == env('APP_UNIVERSAL_TOKEN')) {
            $tokenStatus = 1;    
        }

        if ($token = Token::where("token.token", $request->token)->first()) {
            if ($token['status'] == 'active') {
                $tokenStatus = 1;
            } else {
                $msg = 'Invalid Token, This User was disabled.';
            }
        }

        if ($request->token != env('APP_UNIVERSAL_TOKEN') && User::select("users.*")->where("users.membership_token", $request->token)->first()) {
            $tokenStatus = 0;
            $msg = "Invalid Token. This token is already in use!";
        }

        //Response
        if ($tokenStatus == 1) {
            $user = User::find(Auth::user()->id);
            $user->membership_token = $request->token;
            $user->plan = 'free';
            $user->save();

            return response()->json(['result' => true]);
        } else {
            return response()->json(['result' => false, 'message' => $msg], 400);
        }
    }

    public function updatePlan(Request $request)
    {
        $this->authorize('view-merchant-plans');

        $user = User::find(Auth::user()->id);

        if (!$this->validateToken()) {
            return response()->json(['result' => false, 'message' => 'Invalid token'], 400);
        }

        //reset time period since the merchant changed his plan
        $periods = MonthlyRecurringPlan::where('merchant_id', Auth::user()->id)->get();

        foreach ($periods as $period) {
            DB::table('monthly_recurring_plan_orders')->where('period_id', $period->id)->delete();

            $period->delete();
        }

        if ($request->plan == 'free') {
            $result = ShopifyAdminApi::DeleteRecurringCharge($user);

            if ($result) {
                //Success upgrade
                $resp2 = 'Downgrade Success';

                $user->plan = $request->plan;
                $user->id_recurring_application = 0;
                $user->api_client_id = 0;
                $user->api_status = null;
                $user->plan_updated_at = date('Y-m-d H:i:s');
                $user->save();

                $date = date('Y-m-d');
                $merchant = new MonthlyRecurringPlan();
                $merchant->merchant_id = Auth::user()->id;
                $merchant->start_date = date('Y-m-d H:i:s');
                $merchant->end_date = date('Y-m-d', strtotime($date . ' + 1 month'));
                $merchant->current = true;
                $merchant->save();
            } else {
                //Failure upgrade
                $resp2 = 'Failure Upgrade';
            }
        } else if ($request->plan == 'basic') {
            $result = ShopifyAdminApi::applyRecurringCharge($user, env('PLAN_BASIC_VALUE'));

            Log::info('Recurring - ' . json_encode($result));

            if ($result['id_recurring_application'] > 0) {
                //Success upgrade
                $resp2 = $result['confirmation_url'];

                //$user->plan = $request->plan;
                $user->id_recurring_application = $result['id_recurring_application'];
                $user->api_client_id = $result['api_client_id'];
                $user->api_status = $result['api_status'];
                $user->plan_updated_at = date('Y-m-d H:i:s');
                $user->save();

                $date = date('Y-m-d');
                $merchant = new MonthlyRecurringPlan();
                $merchant->merchant_id = Auth::user()->id;
                $merchant->start_date = date('Y-m-d H:i:s');
                $merchant->end_date = date('Y-m-d', strtotime($date . ' + 1 month'));
                $merchant->current = true;
                $merchant->save();
            } else {
                //Failure upgrade
                $resp2 = 'Failure Upgrade';
            }
        }

        return $resp2;
    }

    public function validateToken()
    {
        $user = User::find(Auth::user()->id);
        $token = Token::where('token.token', $user->membership_token)->first();

        if ($token) {
            if ($token['status'] == 'active') {
                return true;
            }
        }
        if ($user->membership_token == env('APP_UNIVERSAL_TOKEN')) {
            return true;
        }
        return false;
    }

    public function updatePlanSuccess(Request $request)
    {
        if (isset($request['charge_id'])) {
            DB::table('users')->where('id', Auth::user()->id)->update(['plan' => 'basic', 'api_status' => 'accepted']);
        }
        $user = User::find(Auth::user()->id);

        return view('plan-success', ['user' => $user]);
    }

    public function updatePlanFailure()
    {
        return view('plan-failure');
    }

    public function updatePlanUpdate()
    {
        $plan = $_GET['p'];
        $price = 0;

        if ($plan = 'basic') {
            $price = ENV('PLAN_BASIC_VALUE');
        } elseif ($plan = 'advanced') {
            $price = ENV('PLAN_ADVANCED_VALUE');
        }

        return view('plan-update', ['price' => $price]);
    }
}
