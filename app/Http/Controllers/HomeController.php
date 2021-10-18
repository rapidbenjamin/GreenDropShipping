<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\ShopifyWebhook;
use App\Libraries\Shopify\ShopifyAdminApi;
use App\DashboardSteps;
use App\MyProducts;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        //$this->checkWebhook();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $this->authorize('view-merchant-dashboard');

        $steps = DashboardSteps::find(Auth::user()->id);

        if ($steps == null) {

            $steps = new DashboardSteps();

            $steps->id = Auth::user()->id;
            $steps->step1 = 0;
            $steps->step2 = 0;
            $steps->step3 = 0;
            $steps->step4 = 0;
            $steps->step5 = 0;
            $steps->step6 = 0;

            $steps->save();
        }
        $my_products_count = MyProducts::where('id_customer', Auth::user()->id)->count();

        return view('home', array(
            'user' => Auth::user(),
            'steps' => $steps,
            'my_products_count' => $my_products_count,
        ));
    }

    protected function checkWebhook()
    {
        $result = ShopifyAdminApi::getWebhooksList(Auth::user());
        foreach ($result['body']['webhooks'] as $wh) {
            //exist in shopify?
            if ($wh['address'] == 'https://app.greendropship.com/create-order-webkook') {
                $id_webhook = $wh['id'];
                //exist in App?
                $id_shWebhook = ShopifyWebhook::where('id_hook', $id_webhook)->where('id_customer', Auth::user()->id)->first();

                if (!$id_shWebhook) {
                    //Create row
                    $hook = new ShopifyWebhook();
                    $hook->id_customer = Auth::user()->id;
                    $hook->id_hook = $wh['id'];
                    $hook->topic = 'orders/create';
                    $hook->data = json_encode($wh);
                    $hook->save();
                }
            }
        }

        return true;
    }
}
