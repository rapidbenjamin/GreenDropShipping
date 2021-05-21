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
use PDO;

class SyncController extends Controller
{

    public function index()
    {
        return view('settings');
    }

    public function syncStock()
    {
        $t = time();
        echo ('Start: ' . date("h:i:s", $t));
        $stocksData = collect(DB::connection('mysql_magento')->select('SELECT * FROM `mg_inventory_stock_1`'))->where('is_salable', 1);
        $columns = array('Product Id', 'Website Id', 'Stock Id', 'Quantity', 'Is Salable', 'SKU');

        $file = fopen(base_path().'/storage/app/magento_stock.csv', 'w');
        fputcsv($file, $columns);
        foreach ($stocksData as $task) {
            $row['Product Id']  = $task->product_id;
            $row['Website Id']    = $task->website_id;
            $row['Stock Id']    = $task->stock_id;
            $row['Quantity']  = $task->quantity;
            $row['Is Salable']  = $task->is_salable;
            $row['SKU']  = $task->sku;
            fputcsv($file, array($row['Product Id'], $row['Website Id'], $row['Stock Id'], $row['Quantity'], $row['Is Salable'], $row['SKU']));
        }
        fclose($file);
        DB::statement("DROP TABLE IF EXISTS temp_mg_product");
        DB::statement("
        CREATE TABLE `temp_mg_product` (
            `product_id` int(10) NOT NULL,
            `website_id` smallint(5) DEFAULT NULL,
            `stock_id` smallint(5) DEFAULT NULL,
            `quantity` decimal(14,0) DEFAULT NULL,
            `is_salable` smallint(5) DEFAULT NULL,
            `sku` varchar(192) COLLATE utf8_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`product_id`),
            UNIQUE KEY `SKU` (`sku`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
        );
        $path = str_replace("\\", "/", base_path());
        DB::connection()->getpdo()->exec("
            LOAD DATA LOCAL INFILE '".$path."/storage/app/magento_stock.csv' INTO TABLE temp_mg_product
            FIELDS TERMINATED BY ','
            IGNORE 1 LINES"
        );

        DB::statement("
            UPDATE products
            INNER JOIN temp_mg_product ON products.sku = temp_mg_product.sku
            SET products.stock = temp_mg_product.quantity
            WHERE products.stock != temp_mg_product.quantity"
        );
        DB::statement("
            UPDATE my_products
            INNER JOIN temp_mg_product ON my_products.id_product = temp_mg_product.product_id
            SET my_products.cron = 1
            WHERE my_products.stock != temp_mg_product.quantity"
        );

        $myProducts = MyProducts::whereNotNull('inventory_item_id_shopify')->where('cron','1')->get();

        foreach ($myProducts as $mp) {

            try {
                $merchant = User::find($mp->id_customer);
                // GET LOCATION FROM SHOPIFY
                $res = ShopifyAdminApi::getLocationIdForIvewntory($merchant, $mp->inventory_item_id_shopify);
                $mp->location_id_shopify = $res['location_id'];
                $mp->cron=0;
                $mp->save();
                sleep(1);
                //UPDATE STOCK IN SHOPIFY STORES
                $res = ShopifyAdminApi::updateProductIventory($merchant, $mp->id_product, $mp->location_id_shopify, $mp->inventory_item_id_shopify);
                sleep(1);
            } catch (Exception $ex) {
                echo $ex->getMessage();
            }
        }


        $t = time();
        echo ('End: ' . date("h:i:s", $t));
        return 'success';
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

        //echo "<pre>".print_r($result)."</pre>";





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

                //echo $res['result'] . '<br>';
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
                $order->save();

                //Update fulfillment in shopify store

                $user = User::where('id', $order->id_customer)->first();

                //Step 1.  Get shopify order to know item lines
                $shopify_order = ShopifyAdminApi::getOrderInformation($user, $order->id_shopify);

                $i = 0;
                foreach ($shopify_order['body']['order']['line_items'] as $li) {
                    //fulfillmente service validation
                    if ($li['fulfillment_service'] == 'greendropship') {
                        //Step 2.  Get shopify inventory item id
                        $iii = ShopifyAdminApi::getInventoryItemId($user, $li['variant_id']);

                        //Step 3. Get shopify item location id
                        $location = ShopifyAdminApi::getItemLocationId($user, $iii['body']['variant']['inventory_item_id']);

                        //Step 4. Post Tracking Number in shopify
                        $fulfill = ShopifyAdminApi::fulfillItem($user, $location['body']['inventory_levels'][0]['location_id'], $order->tracking_code, $li['id'], $order->id_shopify, $order->shipping_carrier_code);

                        //Step 5. Fulfilled
                        $fulfilled = ShopifyAdminApi::fulfilledOrder($user, $order->id_shopify, $fulfill['body']['fulfillment']['id']);

                        $lines[$i]['line_item_id'] = $li['id'];
                        $lines[$i]['variant_id'] = $li['variant_id'];
                        $lines[$i]['inventory_item_id'] = $iii['body']['variant']['inventory_item_id'];
                        $lines[$i]['location_id'] = $location['body']['inventory_levels'][0]['location_id'];
                        //$lines[$i]['fulfill_status'] = $fulfill;

                        Log::info('PAULO-SHOPIFYFULFILL-RESPONSE');
                        Log::info($fulfill);

                        Log::info('PAULO-SHOPIFYFULFILLED-RESPONSE');
                        Log::info($fulfilled);
                    }

                    $i++;
                }



                //Outputs
                echo 'Tracking updated for order_id' . $order->id;
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
                    $product->id = $item->id;
                    $product->name = $item->name;
                    $product->price = $item->price;
                    //$product->stock = 0;
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
        $t = time();
        echo ('End: ' . date("h:i:s", $t));
        return 'Success';
    }


    public function syncWP()
    {
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
        $rows = Token::where('id', '>', 0)->delete();

        //3. Update table
        foreach ($tokens as $key => $tk) {
            if ($tk->enddate != '0000-00-00 00:00:00') {
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

            echo '<p>Enddate: ' . $tk->enddate . '</p>';
        }




        return 'Success';
    }

    public function getRecursiveCategories($children_data, &$categoriesIds)
    {
        //dd($children_data);
        foreach ($children_data as $Mcategory) {
            try {
                $categoriesIds[] = $Mcategory->id;
                $category = Category::find($Mcategory->id);
                if ($category == null) {
                    $category = new Category();
                    $category->id = $Mcategory->id;
                }
                $category->parent_id = $Mcategory->parent_id;
                $category->name = $Mcategory->name;
                $category->is_active = $Mcategory->is_active;
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


        echo "<p>Starting sync process .... </p>";

        //Process canceled orders

        $orders = Order::where('fulfillment_status', 11)->whereNotNull('magento_order_id')->whereNotNull('magento_entity_id')->get();
        echo "<p>Orders Canceled Process... (" . count($orders) . ")</p>";
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
        echo "<p>Updating pending orders... (" . count($orders) . ")</p>";
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
        echo "<p>Shipping orders sync... (" . count($orders) . ")</p>";
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

        //Process closed orders

        $orders = Order::where('fulfillment_status', 5)->whereNotNull('magento_order_id')->whereNotNull('magento_entity_id')->get();
        echo "<p>Closed orders sync... (" . count($orders) . ")</p>";
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
