<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\ShopifyWebhook;
use App\Libraries\Shopify\ShopifyAdminApi;
use App\DashboardSteps;

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

        $steps = DashboardSteps::find(\Auth::user()->id);

        if($steps == null){

            $steps = new DashboardSteps();

            $steps->id = \Auth::user()->id;
            $steps->step1 = 0;
            $steps->step2 = 0;
            $steps->step3 = 0;
            $steps->step4 = 0;
            $steps->step5 = 0;
            $steps->step6 = 0;

            $steps->save();

        }

        return view('home',Array(
            'user' => \Auth::user(),
            'steps' => $steps,
        ));

    }

    protected function checkWebhook(){
        $result = ShopifyAdminApi::getWebhooksList(Auth::user());

        $existInShopify = 0;

        foreach($result['body']['webhooks'] as $wh){

            //exist in shopify?
            if($wh['address'] == 'https://app.greendropship.com/create-order-webkook'){
                $existInShopify = 1;
                $id_webhook = $wh['id'];
                //exist in App?
                $id_shWebhook = ShopifyWebhook::where('id_hook',$id_webhook)->where('id_customer',$user->id)->first();

                if($id_shWebhook){
                    //Monitoring    
                }else{
                    //Create row
                    $hook = new ShopifyWebhook();
                    $hook->id_customer = $user->id;
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

