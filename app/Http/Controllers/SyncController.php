<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Libraries\Magento\MProduct;
use App\Libraries\Magento\MCategory;
use App\Category;
use App\Products;
use App\MyProducts;
use App\ImportList;
use App\ShopifyWebhook;
use App\Libraries\Magento\MOrder;
use App\Libraries\Shopify\ShopifyAdminApi;
use App\Libraries\SyncLib;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Order;
use App\OrderDetails;
use App\OrderShippingAddress;
use App\User;
use App\Token;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\ProductsToSend;
use App\Settings;
use App\Jobs\ShopifyBulkPublish;
use PDO;

class SyncController extends Controller
{

    public function index()
    {
        return view('settings');
    }

    public function shopifyupgraded()
    {
        SyncLib::shopifyUpgraded();

        //get users with api_status = pending
        $users_list = User::select('id')->where('api_status','accepted')->get();

        foreach ($users_list as $ul) {

            $user = User::find($ul["id"]);

            $res2 = ShopifyAdminApi::getStatusRecurringCharge($user);

            //Validate plan's status
            if($res2 == 'declined' || $res2 == 'expired' || $res2 == 'frozen' || $res2 == 'cancelled'){
                $user->api_status = 'pending';
                $user->plan = 'free';
                $user->save();
            }

        }

        return 'success';
    }

    public function syncStock()
    {
        SyncLib::syncStock();
    }


    public function syncShopifyStock (Request $request) {

        if(!\Session::get('is_bg_running')){
            \Session::put('is_bg_running', 1);
            SyncLib::syncShopifyStock($request);
        }
        else
            echo "Another background process is already running";
        \Session::put('is_bg_running', 0);

    }

    public function arregloSku()
    {
        echo "<P>Lista los webhooks.</P>";

        $user = User::where('id', 121)->first();

        $result = ShopifyAdminApi::getWebhooksList($user);

        $existInShopify = 0;

        $existInApp = 0;

        foreach ($result['body']['webhooks'] as $wh) {

            //exist in shopify?
            if ($wh['address'] == 'https://app.greendropship.com/create-order-webkook') {
                $existInShopify = 1;
                $id_webhook = $wh['id'];
                //exist in App?
                $id_shWebhook = ShopifyWebhook::where('id_hook', $id_webhook)->where('id_customer', $user->id)->first();
                if ($id_shWebhook) {
                    echo "<p>id_webhook: " . $wh['id'] . " - id user: " . $user->id . " --- id DB: " . $id_shWebhook->id . "</p>";
                } else {
                    //Create row
                    $hook = new ShopifyWebhook();
                    $hook->id_customer = $user->id;
                    $hook->id_hook = $wh['id'];
                    $hook->topic = 'orders/create';
                    $hook->data = json_encode($wh);
                    $hook->save();
                    echo "No existe en la base de datos.";
                }
            }
        }
    }



    public function productsToSend()
    {

        echo '<p>Starting process... </p>';

        $productsToSend = ProductsToSend::get();

        foreach ($productsToSend as $pts) {

            try {
                $user = User::find($pts["id_merchant"]);
                $published = true;
                //ShopifyBulkPublish::dispatchNow($user, $pts, $published);

                //$myproduct = MyProducts::select('id_shopify')->where('id_customer', $pts["id_merchant"])->where('id_imp_product', $pts["id_product"]);

                echo '<p>merchant id ' . $pts["id_merchant"] . '  --- id product ' . $pts["id_product"] . '</p>';
                echo print_r($user);

                sleep(10);
            } catch (Exception $ex) {
                echo $ex->getMessage();
            }

            $user = User::where('id', $pts["id_merchant"])->first();
        }


        return 'success';
    }



    public function setTrackingNumber()
    {
        SyncLib::setTrackingNumber();

    }

    public function syncCategories()
    {
        SyncLib::syncCategories();
    }

    public function syncProducts()
    {
        SyncLib::syncProducts();
    }


    public function syncWP()
    {
        SyncLib::syncWP();
    }

    public function updateStatusWhenCancelingMagento()
    {
        SyncLib::updateStatusWhenCancelingMagento();
    }



    public static function GDSLOG($action, $message)
    {
        $log = date('Y-m-d H:i:s') . ' | ' .  $action . ' | ' . $message;
        Storage::disk('local')->append("gds/" . date('Y-m') . '.txt', $log);
    }

    //New way to send bulk productos to shopify
    public function sendProductsToShopify()
    {

        $status = 'failure';

        //Get 10 first 10 products to process
        $products = ProductsToSend::latest()->take(10)->get();


        foreach ($products as $pr) {
            $user = User::where('id', $pr['id_merchant'])->first();

            $settings = Settings::where('id_merchant', $user['id'])->first();

            $published = false;

            if ($settings != null) {

                $published = $settings->set1 == 1;
            }


            ShopifyBulkPublish::dispatchNow($user, $pr, $published);
        }

        return $status;
    }
}
