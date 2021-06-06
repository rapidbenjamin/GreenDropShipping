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
        echo 'Start: ' . gmdate('h:i:s', time());

        $items = collect(DB::connection('mysql_magento')
            ->select('SELECT * FROM `mg_inventory_stock_1`'))
            ->where('is_salable', 1);

        $rows = [];

        foreach ($items as $item) {
            $row = [
                'sku' => $item->sku,
                'quantity' => $item->quantity
            ];

            $rows[] = implode(',', $row);
        }

        Storage::disk('local')->put('magento_stock.csv', implode("\n", $rows));

        DB::statement('TRUNCATE TABLE temp_mg_product');

        $path = str_replace("\\", "/", base_path());

        DB::connection()->getpdo()->exec(
            "LOAD DATA LOCAL INFILE '" . $path . "/storage/app/magento_stock.csv' INTO TABLE temp_mg_product
            FIELDS TERMINATED BY ','"
        );

        DB::statement('UPDATE products P
            INNER JOIN temp_mg_product T
                ON T.sku = P.sku 
            SET P.stock = T.quantity
            WHERE P.stock != T.quantity
        ');

        DB::statement('UPDATE products P
            INNER JOIN my_products MP
                ON MP.id_product = P.id
            INNER JOIN temp_mg_product T
                ON T.sku = P.sku 
            SET
                MP.cron = 1,
                MP.stock = T.quantity
            WHERE MP.stock != T.quantity'
        );

        echo 'End: ' . gmdate('h:i:s', time());

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
                $product = json_decode(Products::find($mp->id_product));
                $price = $product->price / (1 - $mp->profit / 100);
                $merchant = User::find($mp->id_customer);
                // GET LOCATION FROM SHOPIFY IF LOCATION IS NOT SET
                if (!($mp->location_id_shopify > 0)) {
                    $res = ShopifyAdminApi::getLocationIdForIvewntory($merchant, $mp->inventory_item_id_shopify);
                    $mp->location_id_shopify = $res['inventory_levels'][0]['location_id'];
                    sleep(1);
                }
                $mp->cron = 0;
                $mp->save();

                //UPDATE STOCK & COST & PRICE IN SHOPIFY STORES
                ShopifyAdminApi::updateCostPriceStock($merchant, $mp, $price, $product->price);
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
        $t = time();
        echo ('Start: ' . date("h:i:s", $t));

        $continue = true;
        $page = 1;
        $Mtotal_count = $total_count = 0;
        DB::statement(
            "CREATE TABLE IF NOT EXISTS `temp_products` (
                `id` bigint(20) NOT NULL,
                `sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
                `price` double(8,2) NOT NULL,
                `weight` float DEFAULT NULL,
                `type_id` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `status` tinyint(1) DEFAULT NULL,
                `visibility` tinyint(1) DEFAULT NULL,
                `categories` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `images` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `attributes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `stock_info` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `upc` varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
                UNIQUE KEY `sku` (`sku`) USING HASH,
                KEY `id` (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        DB::statement("TRUNCATE TABLE temp_products");
        Storage::disk('local')->delete('magento_products.csv');
        while ($continue) {
            $Mproduct = json_decode(MProduct::get($filter, 255, $page));
            $Mtotal_count = $Mproduct->total_count;
            $Mitems = $Mproduct->items;
            $rows = [];
            foreach ($Mitems as $item) {
                $attribute_upc_index = array_search('upc', array_column($item->custom_attributes, 'attribute_code'));
                $row['id']  = $item->id;
                $row['sku']  = $item->sku;
                $row['name']  = $item->name;
                $row['price']  = $item->price;
                $row['weight']  = isset($item->weight) ? $item->weight : 0;
                $row['type_id']  = $item->type_id;
                $row['status']  = $item->status;
                $row['visibility']  = $item->visibility;
                $row['categories']  = json_encode(isset($item->extension_attributes->category_links) ? $item->extension_attributes->category_links : null);
                $row['images']  = json_encode(isset($item->media_gallery_entries) ? $item->media_gallery_entries : null);
                $row['attributes']  = json_encode(isset($item->custom_attributes) ? $item->custom_attributes : null);
                $row['stock_info']  = json_encode(isset($item->extension_attributes->stock_item) ? $item->extension_attributes->stock_item : null);
                $row['upc']  = $attribute_upc_index ? $item->custom_attributes[$attribute_upc_index]->value : null;
                $rows[] = implode('@', $row);
                $total_count++;
            }
            Storage::disk('local')->append('magento_products.csv', implode("\n", $rows));

            $page++;
            echo 'Num: ' . $total_count . '<br>';
            $continue = $total_count != $Mtotal_count;
        }
        $path = str_replace("\\", "/", base_path());
        DB::connection()->getpdo()->exec(
            "LOAD DATA LOCAL INFILE '" . $path . "/storage/app/magento_products.csv' INTO TABLE temp_products
            FIELDS TERMINATED BY '@'"
        );

        DB::statement(
            "UPDATE products
            INNER JOIN temp_products ON temp_products.sku = products.sku
            INNER JOIN my_products ON my_products.id_product = products.id
            SET my_products.cron = 1
            WHERE temp_products.price != products.price"
        );

        DB::statement(
            "UPDATE products
            INNER JOIN temp_products ON products.sku = temp_products.sku
            SET products.name = temp_products.name,
                products.price = temp_products.price,
                products.weight = temp_products.weight,
                products.type_id = temp_products.type_id,
                products.status = temp_products.status,
                products.visibility = temp_products.visibility,
                products.images = temp_products.images,
                products.attributes = temp_products.attributes,
                products.stock_info = temp_products.stock_info,
                products.upc = temp_products.upc"
        );
        DB::statement(
            "INSERT INTO `products`(`id`,`sku`,`name`,`price`,`stock`,`brand`,`image_url`,`weight`,`type_id`,`status`,`visibility`,`categories`,`images`,`attributes`,`stock_info`,`upc`)
            SELECT temp_products.id, temp_products.sku,temp_products.name,temp_products.price, 0, '', '',temp_products.weight,temp_products.type_id,temp_products.status,temp_products.visibility,temp_products.categories,temp_products.images,temp_products.attributes,temp_products.stock_info, temp_products.upc
            FROM temp_products LEFT JOIN products ON temp_products.sku = products.sku
            WHERE products.sku IS NULL"
        );
        DB::statement(
            "UPDATE products
            LEFT JOIN temp_products ON temp_products.sku = products.sku
            SET stock = 0
            WHERE temp_products.sku IS NULL"
        );
        $t = time();
        echo ('End: ' . date("h:i:s", $t));
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
