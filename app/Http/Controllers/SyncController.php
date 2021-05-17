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

class SyncController extends Controller
{

    public function index()
    {
        return view('settings');
    }

    public function shopifyupgraded()
    {

        //get users with api_status = pending
        $users_list = User::select('id')->where('api_status','pending')->get();

        foreach ($users_list as $ul) {

            $user = User::find($ul["id"]);

            $res = ShopifyAdminApi::getStatusRecurringCharge($user);

            //Validate plan's status
            if($res == 'accepted' || $res == 'active' ){
                $user->api_status = 'accepted';
                $user->plan = 'basic';
                $user->save();
            }

            echo '<p>id user: '.$ul["id"].'</p>';
            echo '<p>request: '.ShopifyAdminApi::getStatusRecurringCharge($user).'</p>';
        }

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
        $t = time();
        echo ('Start: ' . date("h:i:s", $t));


        //UPDATE STOCK FROM MAGENTO TO MIDDLEWARE (this is a View with quantity and saleable setting for each sku)
        $inventory = collect(DB::connection('mysql_magento')->select('SELECT * FROM `mg_inventory_stock_1`'))->where('is_salable', 1);

        foreach ($inventory as $item) {
            $product = Products::find($item->product_id);
            if ($product != null && $product->stock != ($item->quantity+5)) {
                //Writing log
               // self::GDSLOG('Cron Stock', $product->name.' Last Stock: '.$product->stock.' New Stock: '.($item->quantity+5));

                //Update Product Stock
                $product->stock = $item->quantity;
                $product->save();
            }
        };


        $t = time();

        echo ('End: ' . date("h:i:s", $t));
        return 'success';
    }

    public function syncStockUpdateInShopifyStores(){

        //This cron is created to improve performance

        echo "Start: " . date('h:i:s') . "<br>\n";
        $limit = 20;
        $trackId = $_SERVER['UNIQUE_ID']; // rand(10000, 99999);
        // Log::info("{$trackId} Tracking syncStockUpdateInShopifyStores: {$limit}");
        // Log::info("{$trackId} Request: {$_SERVER['REMOTE_ADDR']} - {$_SERVER['HTTP_USER_AGENT']}");
        // DB::update('UPDATE my_products set cron = 0');
        // die();

        //Retrieving location_id and inventory_item_id to store inventory
        $myProducts = MyProducts::whereNotNull('inventory_item_id_shopify')->where('cron', 0)->limit($limit)->get();
        // Log::info("{$trackId} First MyProducts: " . count($myProducts));
        if(count($myProducts) === 0){
            DB::update('UPDATE my_products set cron = 0');
            $myProducts = MyProducts::whereNotNull('inventory_item_id_shopify')->where('cron', 0)->limit($limit)->get();
        }
        // Log::info("{$trackId} Last MyProducts: " . count($myProducts));
        $data = [];
        $productByItem = [];
        $customerNotFound = [];

        foreach ($myProducts as $mp) {
            try {
                $mp->cron = 1;
                $mp->save();
                $product = Products::find($mp->id_product);
                if($product !== null && $mp->stock !== $product->stock){
                    if(isset($data["customer-{$mp->id_customer}"])) {
                        $batchInventory = count($data["customer-{$mp->id_customer}"]['inventory']);
                        if($data["customer-{$mp->id_customer}"]['customer']) {
                            if(count($data["customer-{$mp->id_customer}"]['inventory'][$batchInventory - 1]) < 50){
                                $data["customer-{$mp->id_customer}"]['inventory'][$batchInventory - 1][] = $mp->inventory_item_id_shopify;
                            } else {
                                $data["customer-{$mp->id_customer}"]['inventory'][] = [$mp->inventory_item_id_shopify];
                            }
                        }
                    } else if(!in_array($mp->id_customer, $customerNotFound)) {
                        $user = User::find($mp->id_customer);
                        // Log::info("{$trackId} User: " . json_encode($user));
                        if($user){
                            $data["customer-{$mp->id_customer}"] = [
                                'customer' => $user,
                                'inventory' => [[$mp->inventory_item_id_shopify]]
                            ];
                        } else {
                            $customerNotFound[] = $mp->id_customer;
                        }
                    }
                    $productByItem["item-{$mp->inventory_item_id_shopify}"] = [
                        'MyProduct' => $mp,
                        'ProductModel' => $product
                    ];
                }
            } catch (Exception $ex) {
                echo $ex->getMessage();
            }
        }

        unset($myProducts);
        // Log::info("{$trackId} Customers not found: " . json_encode($customerNotFound));
        // DB::delete('DELETE FROM my_products WHERE id_customer IN (' . implode(',', $customerNotFound) . ')');

        foreach ($data as $item) {
            $access = true;
            foreach ($item['inventory'] as $inventory) {
                if ($access) {
                    $res = ShopifyAdminApi::getLocationIdForIvewntory($item['customer'], implode(',', $inventory));
                    if(count($res) > 0){
                        foreach ($res['inventory_levels'] as $resItem) {
                            $key = "item-{$resItem['inventory_item_id']}";
                            $productByItem[$key]['MyProduct']->location_id_shopify = $resItem['location_id'];
                            $productByItem[$key]['MyProduct']->save();
                        }
                        sleep(10);
                    } else {
                        // Log::info("{$trackId} Customer not found: " . json_encode($data));
                        $item['customer'] = null;
                        $access = false;
                    }
                }
            }
        }
        // Log::info("{$trackId} Data: " . json_encode($data));
        // Log::info("{$trackId} ProductByItem: " . json_encode($productByItem));
        // dump($data, $productByItem);
        // die();

        //UPDATE STOCK IN SHOPIFY STORES
        $cont = 0;
        foreach ($productByItem as $mp) {
            // Oscar desarrolla para cada producto actualizar el stock en cada tienda shopify  
            //Este es el punto 18 del test
            try {
                if (isset($data["customer-{$mp['MyProduct']->id_customer}"])) {
                    $cont++;
                    $res = ShopifyAdminApi::updateProductIventory($data["customer-{$mp['MyProduct']->id_customer}"]['customer'], $mp['ProductModel'], $mp['MyProduct']->location_id_shopify, $mp['MyProduct']->inventory_item_id_shopify);
                    if($res['result'] === 1){
                        $mp['MyProduct']->stock = $mp['ProductModel']->stock;
                        $mp['MyProduct']->save();
                    }
                    // Log::info("{$trackId} {$cont} Res updateProductIventory: " . json_encode($res));
                    sleep(10);
                }
            } catch (Exception $ex) {
                echo $ex->getMessage();
            }
        }

        // Log::info("{$trackId} Success");
        echo 'End: ' . date("h:i:s") . "<br>\n";
        return 'success';
        // die('success');
    }



    public function arregloSku()
    {
            //This function is used by testing
    }



    public function productsToSend(){

        echo '<p>Starting process... </p>';

        $productsToSend = ProductsToSend::get();

        foreach($productsToSend as $pts){

            try {
                $user = User::find($pts["id_merchant"]);
                $published = true;


                echo '<p>merchant id '.$pts["id_merchant"].'  --- id product '.$pts["id_product"].'</p>';
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
        echo "<p>inicio del tracking number</p>";
        $orders = Order::whereNotNull('magento_entity_id')->whereNull('tracking_code')->get();
        foreach ($orders as $order) {
            $querymg = DB::connection('mysql_magento')->select('SELECT *
            FROM `mg_sales_shipment_track` WHERE order_id = ' . $order->magento_entity_id);
            if (count($querymg)) {
                //Update tracking number in middleware DB
                $order->tracking_code = $querymg[0]->track_number;
                $order->fulfillment_status = 6;
                $order->save();

                //Update fulfillment in shopify store

                $user = User::where('id', $order->id_customer)->first();

                //Step 1.  Get shopify order to know item lines
                $shopify_order = ShopifyAdminApi::getOrderInformation($user,$order->id_shopify);

                $i = 0;
                foreach($shopify_order['body']['order']['line_items'] as $li){
                    //fulfillmente service validation
                    if($li['fulfillment_service'] == 'greendropship'){
                        //Step 2.  Get shopify inventory item id
                        $iii = ShopifyAdminApi::getInventoryItemId($user,$li['variant_id']);

                        //Step 3. Get shopify item location id
                        $location = ShopifyAdminApi::getItemLocationId($user,$iii['body']['variant']['inventory_item_id']);

                        //Step 4. Post Tracking Number in shopify
                        $fulfill = ShopifyAdminApi::fulfillItem($user,$location['body']['inventory_levels'][0]['location_id'],$order->tracking_code,$li['id'],$order->id_shopify,$order->shipping_carrier_code);

                        //Step 5. Fulfilled
                        $fulfilled = ShopifyAdminApi::fulfilledOrder($user,$order->id_shopify,$fulfill['body']['fulfillment']['id']);

                        $lines[$i]['line_item_id'] = $li['id'];
                        $lines[$i]['variant_id'] = $li['variant_id'];
                        $lines[$i]['inventory_item_id'] = $iii['body']['variant']['inventory_item_id'];
                        $lines[$i]['location_id'] = $location['body']['inventory_levels'][0]['location_id'];


                    }

                    $i++;
                }

                //Outputs
                Log::info('Tracking updated for order_id' . $order->id);
            }
        }
    }

    public function syncCategories()
    {
        $filter = [
            'searchCriteria[filterGroups][1][filters][0][field]' => 'status',
            'searchCriteria[filterGroups][1][filters][0][value]' => 1,
            'searchCriteria[filterGroups][1][filters][0][condition_type]' => "eq"
        ];
        $categoriesIds = [];
        $t = time();
        echo ('Start: ' . date("h:i:s", $t));
        $this->getRecursiveCategories(json_decode(MCategory::get($filter))->children_data, $categoriesIds);
        DB::table('categories')->whereNotIn('id', $categoriesIds)->delete();
        $t = time();
        echo ('End: ' . date("h:i:s", $t));
        return 'success';
    }

    public function syncProducts()
    {

        $filter = [
            'searchCriteria[filterGroups][0][filters][0][field]' => 'attribute_set_id',
            'searchCriteria[filterGroups][0][filters][0][value]' => 10,
            'searchCriteria[filterGroups][0][filters][0][condition_type]' => "eq",
            'searchCriteria[filterGroups][1][filters][0][field]' => 'status',
            'searchCriteria[filterGroups][1][filters][0][value]' => 1,
            'searchCriteria[filterGroups][1][filters][0][condition_type]' => "eq"
        ];
        $productsIds = [];
        $t = time();
        echo ('Start: ' . date("h:i:s", $t));

        $continue = true;
        $page = 1;
        $Mtotal_count = $total_count = 0;
        while ($continue) {
            $Mproduct = json_decode(MProduct::get($filter, 255, $page));
            $Mitems = $Mproduct->items;
            $Mtotal_count = $Mproduct->total_count; //always the same
            foreach ($Mitems as $item) {
                try {
                    $productsIds[] = $item->id;
                    $product = Products::find($item->id);
                    if ($product == null) {
                        $product = new Products();
                        $product->id = $item->id;
                        $product->sku = $item->sku;
                    }

                    $attribute_upc_index = array_search('upc', array_column($item->custom_attributes, 'attribute_code'));

                    $attribute_upc = NULL;

                    if ($attribute_upc_index !== false) {
                        $attribute_upc = (string) $item->custom_attributes[$attribute_upc_index]->value;
                    }

                    $product->id = $item->id;
                    $product->name = $item->name;
                    $product->price = $item->price;
                    $product->stock = 0;
                    $product->brand = '';
                    $product->upc = $attribute_upc;
                    $product->image_url = '';
                    $product->weight = isset($item->weight) ? $item->weight : 0;
                    $product->type_id = $item->type_id;
                    $product->status = $item->status;
                    $product->visibility = $item->visibility;
                    $product->categories = json_encode(isset($item->extension_attributes->category_links) ? $item->extension_attributes->category_links : null);
                    $product->images = json_encode(isset($item->media_gallery_entries) ? $item->media_gallery_entries : null);
                    $product->stock_info = json_encode(isset($item->extension_attributes->stock_item) ? $item->extension_attributes->stock_item : null);
                    $product->attributes = json_encode(isset($item->custom_attributes) ? $item->custom_attributes : null);
                    $product->save();
                    $total_count++;
                    echo 'SKU: ' . $item->sku . '<br>';
                } catch (Exception $ex) {
                    echo 'Error' . $ex->getMessage() . '-' . $item->sku;;
                }
            }
            $page++;
            echo 'Num: ' . $total_count . '<br>';
            $continue = $total_count != $Mtotal_count;
        }
        DB::table('products')->whereNotIn('id', $productsIds)->delete();
        $t = time();
        echo ('End: ' . date("h:i:s", $t));
        return 'Success';
    }


    public function syncWP(){
        echo '<p>Iniciando sincronizacion de Wordpress</p>';


        //UPDATE TOKENS FROM WORDPRESS DB

        //1. Get collection of records from Wordpress
        $tokens = DB::connection('mysql_wp')
            ->select('SELECT
                wp_rftpn0v78k_pmpro_membership_orders.id AS id_order,
                wp_rftpn0v78k_pmpro_membership_orders.code AS token,
                wp_rftpn0v78k_pmpro_membership_orders.user_id,
                wp_rftpn0v78k_pmpro_memberships_users.status,
                wp_rftpn0v78k_pmpro_memberships_users.enddate,
                wp_rftpn0v78k_users.display_name,
                wp_rftpn0v78k_users.user_email
                FROM wp_rftpn0v78k_pmpro_membership_orders
                JOIN wp_rftpn0v78k_pmpro_memberships_users ON wp_rftpn0v78k_pmpro_memberships_users.user_id = wp_rftpn0v78k_pmpro_membership_orders.user_id
                JOIN wp_rftpn0v78k_users ON wp_rftpn0v78k_users.id = wp_rftpn0v78k_pmpro_membership_orders.user_id
                WHERE wp_rftpn0v78k_pmpro_memberships_users.status = "active"
            ');

        //2. Clean Middeware token table
            $rows = Token::where('id','>',0)->delete();

        //3. Update table
            foreach ($tokens as $key => $tk) {
                if($tk->enddate != '0000-00-00 00:00:00'){
                $token = new Token;
                $token->token = $tk->token;
                $token->status = $tk->status;
                $token->id_order = $tk->id_order;
                $token->user_id = $tk->user_id;
                $token->enddate = $tk->enddate;
                $token->display_name = $tk->display_name;
                $token->user_email = $tk->user_email;
                $token->save();
                }

                echo '<p>Enddate: '.$tk->enddate.'</p>';
            }

        return 'Success';
    }

    public function getRecursiveCategories($children_data, &$categoriesIds)
    {

        foreach ($children_data as $Mcategory) {
            try {
                $categoriesIds[] = $Mcategory->id;
                $category = Category::find($Mcategory->id);
                if ($category == null) {
                    $category = new Category();
                    $category->id = $Mcategory->id;
                    $category->is_active = $Mcategory->is_active;
                }
                $category->parent_id = $Mcategory->parent_id;
                $category->name = $Mcategory->name;
                //$category->is_active = $Mcategory->is_active;
                $category->level = $Mcategory->level;
                $category->position = $Mcategory->position;
                $category->save();
                //echo 'Id: '. $category->id . '<br>';
                if (count($Mcategory->children_data)) {
                    $this->getRecursiveCategories($Mcategory->children_data, $categoriesIds);
                }
            } catch (Exception $ex) {
                echo 'Error' . $ex->getMessage();
            }
        }
    }

    public function updateStatusWhenCancelingMagento()
    {


        ECHO "<p>Starting sync process .... </p>";

        //Process canceled orders

        $orders = Order::where('fulfillment_status', 11)->whereNotNull('magento_order_id')->whereNotNull('magento_entity_id')->get();
        ECHO "<p>Orders Canceled Process... (".count($orders).")</p>";
        foreach ($orders as $order) {
            $orderM = DB::connection('mysql_magento')->select('SELECT * FROM `mg_sales_order` WHERE entity_id = ' . $order->magento_entity_id);
            if (count($orderM)) {
                if ($orderM[0]->status == 'canceled' && $orderM[0]->state == 'canceled') {
                    $order->fulfillment_status = 9;
                    $order->financial_status = 3;
                    $order->save();
                    echo 'order: ' . $order->id . ' has updated its status<br>';
                }
            }
        }

        //Update state Pending to Processing
 /*
        $orders = Order::where('fulfillment_status', 5)->whereNotNull('magento_order_id')->whereNotNull('magento_entity_id')->get();
        ECHO "<p>Updating pending orders... (".count($orders).")</p>";
        foreach ($orders as $order) {
            $orderM = DB::connection('mysql_magento')->select('SELECT * FROM `mg_sales_order` WHERE entity_id = ' . $order->magento_entity_id);
            if (count($orderM)) {
                if ($orderM[0]->status == 'pending' && $orderM[0]->state == 'new') {
                    $changeM = DB::connection('mysql_magento')->update('update `mg_sales_order` SET `status` = "processing",`state` = "processing" WHERE entity_id = ' . $order->magento_entity_id);
                    $changeM2 = DB::connection('mysql_magento')->update('update `mg_sales_order_status_history` SET `status` = "processing" WHERE parent_id = ' . $order->magento_entity_id);
                    $changeM3 = DB::connection('mysql_magento')->update('update `mg_sales_order_grid` SET `status` = "processing" WHERE entity_id = ' . $order->magento_entity_id);
                    echo 'order: ' . $order->id . ' has updated its status in magento<br>';
                }
            }
        }



        //Process shipping orders
        $orders = Order::where('fulfillment_status', 5)->whereNotNull('magento_order_id')->whereNotNull('magento_entity_id')->get();
        ECHO "<p>Shipping orders sync... (".count($orders).")</p>";
        foreach ($orders as $order) {
            $orderM = DB::connection('mysql_magento')->select('SELECT * FROM `mg_sales_order` WHERE entity_id = ' . $order->magento_entity_id);
            if (count($orderM)) {
                if ($orderM[0]->status == 'complete' && $orderM[0]->state == 'complete') {
                    $order->fulfillment_status = 6;
                    $order->financial_status = 2;
                    $order->save();
                    echo 'order: ' . $order->id . ' has updated its status<br>';
                }
            }
        }
*/


        //Process closed orders

        $orders = Order::where('fulfillment_status', 5)->whereNotNull('magento_order_id')->whereNotNull('magento_entity_id')->get();
        ECHO "<p>Closed orders sync... (".count($orders).")</p>";
        foreach ($orders as $order) {
            $orderM = DB::connection('mysql_magento')->select('SELECT * FROM `mg_sales_order` WHERE entity_id = ' . $order->magento_entity_id);
            if (count($orderM)) {
                if ($orderM[0]->status == 'closed' && $orderM[0]->state == 'closed') {
                    $order->fulfillment_status = 6;
                    $order->financial_status = 2;
                    $order->save();
                    echo 'order: ' . $order->id . ' has updated its status<br>';
                }
            }
        }

        return 'success';
    }



    public static function GDSLOG($action, $message)
    {
        $log = date('Y-m-d H:i:s') . ' | ' .  $action . ' | ' . $message;
        Storage::disk('local')->append("gds/" . date('Y-m') . '.txt', $log);
    }

    //New way to send bulk productos to shopify
    public function sendProductsToShopify(){

        $status = 'failure';

        //Get 10 first 10 products to process
        $products = ProductsToSend::latest()->take(10)->get();


        foreach($products as $pr){
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
