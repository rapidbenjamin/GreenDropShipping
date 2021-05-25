<?php

namespace App\Console;


use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
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

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {



          //SHOPIFY UPGRATED
        $schedule->call(function(){

            //get users with api_status = pending
            $users_list = User::select('id')->where('api_status','pending')->get();
    
            foreach ($users_list as $ul) {
                    
                $user = User::find($ul["id"]);
                    
                //Validate plan's status    
                if(ShopifyAdminApi::getStatusRecurringCharge($user) == 'accepted'){
                    $user->api_status = 'accepted';
                    $user->plan = 'basic';
                    $user->save();
                }    
                
            }
            
            echo "<p>Shopify Upgraded Cron Job Complete</p>";
            
        })->everyMinute();   

        //STOCK
        $schedule->call(function(){
            //UPDATE STOCK FROM MAGENTO TO MIDDLEWARE (this is a View with quantity and saleable setting for each sku)
            echo "<p>Starting Stock Cron Job</p>";
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
            
            
        })->everyFiveMinutes();
        
        //Tracking Number
        $schedule->call(function(){
            $orders = Order::whereNotNull('magento_entity_id')->whereNull('tracking_code')->get();
            foreach ($orders as $order) {
                $querymg = DB::connection('mysql_magento')->select('SELECT * 
                FROM `mg_sales_shipment_track` WHERE order_id = ' . $order->magento_entity_id);
                if (count($querymg)) {
                    //Update tracking number in middleware DB
                    $order->tracking_code = $querymg[0]->track_number;
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
        })->everyFiveMinutes();       
        
           

         //Update WP membershiop tokens
        $schedule->call(function(){

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
    
                }

        })->everyFiveMinutes(); 
 


        //Update Order Status
        $schedule->call(function(){

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
    
    
    
            //Process orders with status shipped and closed
            $orders = Order::where('fulfillment_status', 5)
                ->whereNotNull('magento_order_id')
                ->whereNotNull('magento_entity_id')
                ->get();

            foreach ($orders as $order) {
                $orderM = DB::connection('mysql_magento')->select('SELECT * FROM `mg_sales_order` WHERE entity_id = ' . $order->magento_entity_id);

                if (count($orderM)) {
                    if ($orderM[0]->status == 'complete' && $orderM[0]->state == 'complete') {
                        $order->fulfillment_status = 6;
                        $order->financial_status = 2;
                        $order->save();

                        echo 'Order: ' . $order->id . ' has updated it\'s status to Shipped<br>';
                    }

                    if ($orderM[0]->status == 'closed' && $orderM[0]->state == 'closed') {
                        $order->fulfillment_status = 9;
                        $order->financial_status = 3;
                        $order->save();

                        echo 'Order: ' . $order->id . ' has updated it\'s status to Refunded<br>';
                    }
                }
            }
    
            //Process closed orders
            /*
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
            */

        })->everyFiveMinutes(); 


        //Magento Categories
        $schedule->call(function(){

            $filter = [
                'searchCriteria[filterGroups][1][filters][0][field]' => 'status',
                'searchCriteria[filterGroups][1][filters][0][value]' => 1,
                'searchCriteria[filterGroups][1][filters][0][condition_type]' => "eq"
            ];
            $categoriesIds = [];
            $this->getRecursiveCategories(json_decode(MCategory::get($filter))->children_data, $categoriesIds);
            DB::table('categories')->whereNotIn('id', $categoriesIds)->delete();

        })->everyFiveMinutes(); 
 
 
        //Magento Products
        $schedule->call(function(){

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
                    $product->id = $item->id;
                    $product->name = $item->name;
                    $product->price = $item->price;
                    $product->stock = 0;
                    $product->brand = '';
                    $product->upc = '';
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

        })->everyFiveMinutes();
 
    }//Close schedule



    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
