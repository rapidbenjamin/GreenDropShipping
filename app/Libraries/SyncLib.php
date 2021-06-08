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
        DB::statement("TRUNCATE TABLE temp_mg_product");
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
                $price = $product->price * (100 + $mp->profit) / 100;
                $merchant = User::find($mp->id_customer);
                // GET LOCATION FROM SHOPIFY IF LOCATION IS NOT SET
                if (!($mp->location_id_shopify > 0)) {
                    $res = ShopifyAdminApi::getLocationIdForIvewntory($merchant, $mp->inventory_item_id_shopify);
                    $mp->location_id_shopify = $res['inventory_levels'][0]['location_id'];
                    sleep(1);
                }
                $mp->cron = 0;
                $mp->save();
                $mp->sku = $product->sku;
                $mp->barcode = $product->upc;

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

        $t = time();
        echo ('Start: ' . date("h:i:s", $t));

        DB::statement("TRUNCATE TABLE temp_products");
        Storage::disk('local')->delete('magento_products.csv');

        $file = file_get_contents('https://members.greendropship.com/downloads/products.csv');
        Storage::disk('local')->put('magento_products.csv', $file);
        $path = str_replace("\\", "/", base_path());
        DB::connection()->getpdo()->exec(
            'LOAD DATA INFILE "' . $path . '/storage/app/magento_products.csv"
            INTO TABLE temp_products
            COLUMNS TERMINATED BY ","
            OPTIONALLY ENCLOSED BY "\""
            ESCAPED BY "<"
            ESCAPED BY ">"
            ESCAPED BY "\'"
            IGNORE 1 LINES'
        );

        DB::statement(
            "UPDATE products P
            INNER JOIN temp_products T ON T.sku = P.sku
            INNER JOIN my_products M ON M.id_product = P.id
            SET M.cron = 1
            WHERE T.price != P.price"
        );

        DB::statement(
            "UPDATE products P
            INNER JOIN temp_products T ON P.sku = T.sku
            SET P.name = T.name,
                P.price = T.price,
                P.stock = T.qty,
                P.weight = T.weight,
                P.type_id = 'simple',
                P.status = 1,
                P.image_url = T.images_1,
                P.images = JSON_ARRAY(
                    JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_1, 60)),
                    JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_2, 60)),
                    JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_3, 60)),
                    JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_4, 60))
                ),
                P.attributes = JSON_ARRAY(
                    JSON_OBJECT('attribute_code', 'image', 'value', SUBSTR(T.images_1, 60)),
                    JSON_OBJECT('attribute_code', 'description', 'value', T.description),
                    JSON_OBJECT('attribute_code', 'ship_width', 'value', T.width),
                    JSON_OBJECT('attribute_code', 'ship_length', 'value', T.length),
                    JSON_OBJECT('attribute_code', 'ship_height', 'value', T.height),
                    JSON_OBJECT('attribute_code', 'brand', 'value', T.brand),
                    JSON_OBJECT('attribute_code', 'upc', 'value', T.upc),
                    JSON_OBJECT('attribute_code', 'cube', 'value', T.cubic),
                    JSON_OBJECT('attribute_code', 'size', 'value', T.size),
                    JSON_OBJECT('attribute_code', 'size_uom', 'value', T.size_uom),
                    JSON_OBJECT('attribute_code', 'storage', 'value', '')
                ),
                P.stock_info = T.storage,
                P.upc = T.upc"
        );

        DB::statement(
            "INSERT INTO `products`(`sku`,`name`,`price`,`stock`,`brand`,`image_url`,`weight`,`type_id`,`status`,`images`,`attributes`,`stock_info`,`upc`)
            SELECT T.sku,T.name,T.price, T.qty, T.brand, SUBSTR(T.images_1, 60),T.weight,'simple',1,JSON_ARRAY(
                    JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_1, 60)),
                    JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_2, 60)),
                    JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_3, 60)),
                    JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_4, 60))
                ),JSON_ARRAY(
                    JSON_OBJECT('attribute_code', 'image', 'value', SUBSTR(T.images_1, 60)),
                    JSON_OBJECT('attribute_code', 'description', 'value', T.description),
                    JSON_OBJECT('attribute_code', 'ship_width', 'value', T.width),
                    JSON_OBJECT('attribute_code', 'ship_length', 'value', T.length),
                    JSON_OBJECT('attribute_code', 'ship_height', 'value', T.height),
                    JSON_OBJECT('attribute_code', 'brand', 'value', T.brand),
                    JSON_OBJECT('attribute_code', 'upc', 'value', T.upc),
                    JSON_OBJECT('attribute_code', 'cube', 'value', T.cubic),
                    JSON_OBJECT('attribute_code', 'size', 'value', T.size),
                    JSON_OBJECT('attribute_code', 'size_uom', 'value', T.size_uom),
                    JSON_OBJECT('attribute_code', 'storage', 'value', '')
                ),T.storage, T.upc
            FROM temp_products T LEFT JOIN products P ON T.sku = P.sku
            WHERE P.sku IS NULL"
        );
        DB::statement(
            "UPDATE products P
            LEFT JOIN temp_products T ON T.sku = P.sku
            SET stock = 0
            WHERE T.sku IS NULL"
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
