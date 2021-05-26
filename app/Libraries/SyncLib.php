<?php

namespace App\Libraries;

use Illuminate\Http\Request;
use App\MyProducts;
use App\User;
use App\Order;
use App\Token;
use App\Category;
use App\Products;
use App\Libraries\Shopify\ShopifyAdminApi;
use App\Libraries\Magento\MCategory;
use App\Libraries\Magento\MProduct;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SyncLib
{

    public static function shopifyUpgraded()
    {
        //get users with api_status = pending
        $users_list = User::select('id')->where('api_status', 'pending')->get();
        foreach ($users_list as $ul) {
            $user = User::find($ul["id"]);
            $res = ShopifyAdminApi::getStatusRecurringCharge($user);
            //Validate plan's status
            if ($res == 'accepted' || $res == 'active') {
                $user->api_status = 'accepted';
                $user->plan = 'basic';
                $user->save();
            }
            echo '<p>id user: ' . $ul["id"] . '</p>';
        }
        return 'success';
    }

    public static function syncStock()
    {
        $t = time();
        echo ('Start: ' . date("h:i:s", $t));
        $stocksData = collect(DB::connection('mysql_magento')->select('SELECT * FROM `mg_inventory_stock_1`'))->where('is_salable', 1);
        $rows = [];
        foreach ($stocksData as $task) {
            $row['product_id']  = $task->product_id;
            $row['quantity']  = $task->quantity;
            $row['sku']  = $task->sku;
            $rows[] = implode(',', $row);
        }
        Storage::disk('local')->put('magento_stock.csv', implode("\n", $rows));
        DB::statement(
            "CREATE TABLE IF NOT EXISTS `temp_mg_product` (
            `product_id` int(10) NOT NULL,
            `quantity` decimal(14,0) DEFAULT NULL,
            `sku` varchar(192) COLLATE utf8_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`product_id`),
            UNIQUE KEY `SKU` (`sku`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
        );
        DB::statement("TRUNCATE TABLE temp_mg_product");
        $path = str_replace("\\", "/", base_path());
        DB::connection()->getpdo()->exec(
            "LOAD DATA LOCAL INFILE '" . $path . "/storage/app/magento_stock.csv' INTO TABLE temp_mg_product
            FIELDS TERMINATED BY ','"
        );

        DB::statement(
            "UPDATE products
            INNER JOIN temp_mg_product ON products.sku = temp_mg_product.sku
            SET products.stock = temp_mg_product.quantity
            WHERE products.stock != temp_mg_product.quantity"
        );
        DB::statement(
            "UPDATE my_products
            INNER JOIN temp_mg_product ON my_products.id_product = temp_mg_product.product_id
            SET my_products.cron = 1, my_products.stock = temp_mg_product.quantity
            WHERE my_products.stock != temp_mg_product.quantity"
        );

        $t = time();
        echo ('End: ' . date("h:i:s", $t));
        return 'success';
    }

    public static function syncShopifyStock($request)
    {
        $myProducts = MyProducts::whereNotNull('inventory_item_id_shopify');
        if (isset($request) && $request->filled('user_id') && $request->user_id > 0)
            $myProducts = $myProducts->where('id_customer', $request->user_id)->take(50);
        $myProducts = $myProducts->where('cron', '1')->get();
        $updatedCount = 0;
        foreach ($myProducts as $mp) {
            try {
                $merchant = User::find($mp->id_customer);
                echo var_dump($merchant);
                // GET LOCATION FROM SHOPIFY IF LOCATION IS NOT SET
                if (!($mp->location_id_shopify > 0)) {
                    $res = ShopifyAdminApi::getLocationIdForIvewntory($merchant, $mp->inventory_item_id_shopify);
                    $mp->location_id_shopify = $res['location_id'];
                    sleep(1);
                }
                $mp->cron = 0;
                $mp->save();

                //UPDATE STOCK IN SHOPIFY STORES
                $res = ShopifyAdminApi::updateProductIventory($merchant, $mp, $mp->location_id_shopify, $mp->inventory_item_id_shopify);
                sleep(1);
                $updatedCount++;
            } catch (Exception $ex) {
                echo $ex->getMessage();
            }
        }
        echo $updatedCount . " items' stock has been updated";
    }

    public static function setTrackingNumber()
    {
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
                    }
                    $i++;
                }
            }
        }
    }

    public static function syncWP()
    {
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
        Token::where('id', '>', 0)->delete();

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
        }
        return 'Success';
    }

    public static function updateStatusWhenCancelingMagento()
    {
        $orders = Order::where('fulfillment_status', 11)->whereNotNull('magento_order_id')->whereNotNull('magento_entity_id')->get();
        echo count($orders);
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

        //Update state Pending to Processing, shipping orders and closed orders
        $orders = Order::where('fulfillment_status', 5)->whereNotNull('magento_order_id')->whereNotNull('magento_entity_id')->get();
        echo count($orders);
        foreach ($orders as $order) {
            $orderM = DB::connection('mysql_magento')->select('SELECT * FROM `mg_sales_order` WHERE entity_id = ' . $order->magento_entity_id);
            if (count($orderM)) {
                if ($orderM[0]->status == 'pending' && $orderM[0]->state == 'new') {
                    DB::connection('mysql_magento')->update('update `mg_sales_order` SET `status` = "processing",`state` = "processing" WHERE entity_id = ' . $order->magento_entity_id);
                    DB::connection('mysql_magento')->update('update `mg_sales_order_status_history` SET `status` = "processing" WHERE parent_id = ' . $order->magento_entity_id);
                    DB::connection('mysql_magento')->update('update `mg_sales_order_grid` SET `status` = "processing" WHERE entity_id = ' . $order->magento_entity_id);
                    echo 'order: ' . $order->id . ' has updated its status in magento<br>';
                }
                if (($orderM[0]->status == 'complete' && $orderM[0]->state == 'complete') || ($orderM[0]->status == 'closed' && $orderM[0]->state == 'closed')) {
                    $order->fulfillment_status = 6;
                    $order->financial_status = 2;
                    $order->save();
                    echo 'order: ' . $order->id . ' has updated its status<br>';
                }
            }
        }
        return 'success';
    }

    public static function syncCategories()
    {
        $filter = [
            'searchCriteria[filterGroups][1][filters][0][field]' => 'status',
            'searchCriteria[filterGroups][1][filters][0][value]' => 1,
            'searchCriteria[filterGroups][1][filters][0][condition_type]' => "eq"
        ];
        $categoriesIds = [];
        (new SyncLib())->getRecursiveCategories(json_decode(MCategory::get($filter))->children_data, $categoriesIds);
        DB::table('categories')->whereNotIn('id', $categoriesIds)->delete();
        return 'success';
    }

    public static function syncProducts()
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
}
