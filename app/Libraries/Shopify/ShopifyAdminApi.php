<?php

namespace App\Libraries\Shopify;

use App\MyProducts;
use App\Products;
use App\Settings;
use App\ShopifyWebhook;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Product;

class ShopifyAdminApi
{
    public static function createProduct($user, $product, $published = false)
    {
        $inventory_quantity = 0;
        $productModel = Products::where('sku', $product['sku'])->first();
        if ($productModel != null) {
            $inventory_quantity = $productModel->stock;
        }

        $result = ShopifyAdminApi::request($user, 'POST', '/admin/api/2020-07/products.json', json_encode(
            array(
                'product' => array(
                    "title" => $product['name'],
                    "body_html" => $product['description'],
                    "published_at" => date("Y-m-d\TH:i:s"),
                    "product_type" => $product['product_type'],
                    "published_scope" => 'global',
                    "tags" => $product['tags'],
                    "published" => $published,
                    "vendor" => 'GreenDropShip',
                    "variants" => array(
                        0 => array(
                            "weight" => $product['weight'],
                            "price" => (float)$product['price'],
                            "sku" => $product['sku'],
                            "fulfillment_service" => "Greendropship",
                            "inventory_management" => "Greendropship",
                            "inventory_quantity" => $inventory_quantity
                        )
                    )
                )
            )
        ));


        if (isset($result['HTTP_CODE']) && $result['HTTP_CODE'] == 201) {



            return array(
                'result' => 1,
                'shopify_id' => $result['body']['product']['id'],
                'variant_id' => $result['body']['product']['variants'][0]['id'],
                'inventory_item_id' => $result['body']['product']['variants'][0]['inventory_item_id'],
                'images' => $product['images']
            );
        } else if (isset($result['HTTP_CODE']) && ($result['HTTP_CODE'] == 429)) {
            return array(
                'result' => 2,
                'retry-after' => $result['retry-after']
            );
        } else {
            throw new Exception("can't publish product to shopofy -> HTTP_CODE: " . $result['HTTP_CODE'] . '-> stack: ' . $product['name']);
        }
    }


    public static function updateCost($user, $id_shopify, $inventory_item_id, $cost)
    {

        $result = ShopifyAdminApi::request($user, 'PUT', '/admin/api/2020-10/inventory_items/'.$inventory_item_id.'.json', json_encode(
            array(
                'inventory_item' => array(
                    "id" => $id_shopify,
                    "cost" => $cost
                    )
                )
            )
        );
    }



    public static function getShopInformation($user)
    {
        $result = ShopifyAdminApi::request(
            $user,
            'GET',
            '/admin/api/2020-10/shop.json'
        );

        return $result['body']['shop'];

    }



    public static function publicImageProduct($user, $shopify_id, $image)
    {
        $result = ShopifyAdminApi::request($user, 'POST', '/admin/api/2020-07/products/' . $shopify_id . '/images.json', json_encode(
            array(
                'image' => array(
                    'src' => $image
                )
            )
        ));

        if (isset($result['HTTP_CODE']) && ($result['HTTP_CODE'] == 200)) {
            return array(
                'result' => 1,
            );
        } else if (isset($result['HTTP_CODE']) && ($result['HTTP_CODE'] == 429)) {
            return array(
                'result' => 2,
                'retry-after' => $result['retry-after']
            );
        } else {
            throw new Exception("can't publish image of product to shopofy -> HTTP_CODE: " . $result['HTTP_CODE'] . '->stack: ' . $image);
        }
    }

    public static function getCollections($user)
    {
        $resultCustomCollectons = ShopifyAdminApi::request($user, 'GET', '/admin/api/2020-10/custom_collections.json');

        if (isset($resultCustomCollectons['HTTP_CODE']) && $resultCustomCollectons['HTTP_CODE'] == 200) {
            $collections = collect();
            $custom_collection = $resultCustomCollectons['body']['custom_collections'];

            foreach ($custom_collection as $item) {
                $collections->push(array(
                    'id' => $item['id'],
                    'name' => $item['title'],
                    'type' => 'custom',
                ));
            }

            return $collections;
        }
        return null;
    }

    public static function createCustomCollection($user, $collection)
    {
        $result = ShopifyAdminApi::request($user, 'POST', '/admin/api/2020-07/custom_collections.json', json_encode(
            array(
                'custom_collection' => array(
                    'title' => $collection
                )
            )
        ));
        if (isset($result['HTTP_CODE']) && $result['HTTP_CODE'] == 201) {
            return $result['body']['custom_collection']['id'];
        }
        return 0;
    }

    public static function addProductToCustomCollection($user, $product_id, $collection_id)
    {
        $result = ShopifyAdminApi::request($user, 'POST', '/admin/api/2020-07/collects.json', json_encode(
            array(
                'collect' => array(
                    'product_id' => $product_id,
                    'collection_id' => $collection_id
                )
            )
        ));
        if (isset($result['HTTP_CODE']) && $result['HTTP_CODE'] == 201) {
            return true;
        }
        return 0;
    }

    public static function deleteProduct($user, $shopify_product_id)
    {
        return ShopifyAdminApi::request($user, 'DELETE', '/admin/api/2020-07/products/' . $shopify_product_id . '.json');
    }

    public static function createWebhook($user, $topic, $address)
    {

            //get the webhook code create orders
            $result = ShopifyAdminApi::request(
                $user,
                'POST',
                '/admin/api/2020-07/webhooks.json',
                json_encode(
                    array(
                        'webhook' => array(
                            'topic' => $topic,
                            'address' => $address,
                            'format' => 'json'
                        )
                    )
                )
            );

        $hook = ShopifyWebhook::where('id_customer', $user->id)->where('topic', $topic)->first();




        if ($hook == null) {
            $hook = new ShopifyWebhook();
            $hook->id_customer = $user->id;
            $hook->id_hook = $result['body']['webhook']['id'];
            $hook->topic = $topic;
            $hook->data = json_encode($result['body']['webhook']);
            $hook->save();
        }elseif(isset($result['body']['webhook']['id'])){
            $id_hook = $result['body']['webhook']['id'];
            $data = json_encode($result['body']['webhook']);
            ShopifyWebhook::where('id_customer', $user->id)->update(['id_hook' => $id_hook]);
            ShopifyWebhook::where('id_customer', $user->id)->update(['data' => $data]);
        }

    }


    //Webhooks List
    public static function getWebhooksList($user){

       return ShopifyAdminApi::request(
            $user,
            'GET',
            '/admin/api/2021-01/webhooks.json'
        );

    }


    public static function applyRecurringCharge($user, $plan_price)
    {
        $result = ShopifyAdminApi::request($user, 'POST', '/admin/api/2020-10/recurring_application_charges.json', json_encode(array(


            "recurring_application_charge" => array(
                "name" => "Basic Plan",
                "test" => false, //change after sending shopify
                'price' => $plan_price, //dollars
                'return_url' => env('APP_URL').'/plans/update-success' //redirecciona después de que se confirma/rechaza la suscripción.
            )
        )));


        if (isset($result['HTTP_CODE']) && $result['HTTP_CODE'] == 201) {
            return array(
                'id_recurring_application' => $result['body']['recurring_application_charge']['id'],
                'api_client_id' => $result['body']['recurring_application_charge']['api_client_id'],
                'api_status' => $result['body']['recurring_application_charge']['status'],
                'confirmation_url' => $result['body']['recurring_application_charge']['confirmation_url'],
                'success' => 1
            );
        }
        return null;

    }

    public static function getStatusRecurringCharge($user){
        $result = ShopifyAdminApi::request(
            $user,
            'GET',
            '/admin/api/2021-01/recurring_application_charges/'.$user->id_recurring_application.'.json'
        );

        if (isset($result['HTTP_CODE']) && $result['HTTP_CODE'] == 200){
            return $result['body']['recurring_application_charge']['status'];
        }

        return false;

    }

    public static function deleteRecurringCharge($user)
    {


        $result = ShopifyAdminApi::request($user, 'DELETE', '/admin/api/2021-01/recurring_application_charges/'.$user->id_recurring_application.'.json');

        if (isset($result['HTTP_CODE']) && $result['HTTP_CODE'] == 200) {
            return true;
        }
        //return null; //Cambiar para produccion
        return true;

    }

    //deprecated
    public static function updateProductIventory($user, $productModel, $location_id, $inventory_item_id)
    {
        $inventory_quantity = 0;
        if ($productModel != null) {
            $inventory_quantity = $productModel->stock;
        }

        $result = ShopifyAdminApi::request(
            $user,
            'POST',
            '/admin/api/2021-01/inventory_levels/set.json',
            json_encode(
                array(
                    "location_id" => $location_id,
                    "inventory_item_id" => $inventory_item_id,
                    "available" => $inventory_quantity
                )
            )
        );



        if (isset($result['HTTP_CODE']) && $result['HTTP_CODE'] == 200) {
            return array(
                'result' => 1,
                'variante_id' => $result['body']['inventory_level']['inventory_item_id'],
            );
        } else if (isset($result['HTTP_CODE']) && ($result['HTTP_CODE'] == 429)) {
            return array(
                'result' => 2,
                'retry-after' => $result['retry-after']
            );
        } else {
            throw new Exception("can't update product inventory to shopofy -> HTTP_CODE: " . $result['HTTP_CODE'] . '-> stack: ' . $productModel->sku);
        }
    }


    public static function getLocationIdForIvewntory($user, $inventory_item_ids)
    {
        $result = ShopifyAdminApi::request(
            $user,
            'GET',
            '/admin/api/2021-01/inventory_levels.json?inventory_item_ids='.$inventory_item_ids
        );

        if (isset($result['HTTP_CODE']) && $result['HTTP_CODE'] == 200) {
            return array(
                'result' => 1,
                'inventory_levels' => $result['body']['inventory_levels'],
                // 'location_id' => $result['body']['inventory_levels'][0]['location_id'],
                // 'inventory_item_id' => $result['body']['inventory_levels'][0]['inventory_item_id'],
            );
        } else if (isset($result['HTTP_CODE']) && ($result['HTTP_CODE'] == 429)) {
            return array(
                'result' => 2,
                'retry-after' => $result['retry-after']
            );
        } else {
            return [];
            throw new Exception("can't update product inventory to shopofy -> HTTP_CODE: " . $result['HTTP_CODE'] . '-> stack: ' . $inventory_item_ids);
        }
    }



    public static  function request($user, $method, $url, $data = '')
    {
        //echo "https://" . $user->shopify_url . $url . '<br>';
        $ch = curl_init("https://" . $user->shopify_url . $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'X-Shopify-Access-Token: ' . $user->shopify_token, 'Content-Length: ' . strlen($data)));
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = substr($result, $header_size);
        curl_close($ch);
        $body = json_decode($body, true);
        $call_limit = '';
        $retry_after = '';

        foreach (explode("\n", $header) as $part) {
            $middle = explode(":", $part, 2);
            if (count($middle) < 2) continue;
            $header_line = trim($middle[0]);
            if ($header_line == 'x-shopify-shop-api-call-limit') {
                $call_limit = trim($middle[1]);
            }
            if ($header_line == 'retry-after') {
                $retry_after = trim($middle[1]);
            }
        }
        return array('HTTP_CODE' => $httpcode, 'x-shopify-shop-api-call-limit' => $call_limit, 'retry-after' => $retry_after, 'body' => $body);
    }


    //Create FulfillmentService
    public static function createFulfillmentService($user){

        $result = ShopifyAdminApi::request(
            $user,
            'POST',
            '/admin/api/2020-10/fulfillment_services.json',
            json_encode(
                array(
                    "fulfillment_service" => array(
                        "name" => "Greendropship",
                        "callback_url" => "http://google.com",
                        "inventory_management" => true,
                        "tracking_support" => true,
                        "requires_shipping_method" => true,
                        "format" => "json"
                    )
                )
            )
        );



        if (isset($result['HTTP_CODE']) && $result['HTTP_CODE'] == 201) {

            $user->fulfillment_installed = 1;
            $user->fulfillment_service_id = $result['body']['fulfillment_service']['id'];
            $user->fulfillment_location_id = $result['body']['fulfillment_service']['location_id'];
            $user->save();

            ShopifyAdminApi::putFulfillmentService($user,$result['body']['fulfillment_service']['id']);

            return array(
                'result' => true
            );
        } else {
            throw new Exception("Can't create a fulfillment service. -> HTTP_CODE: " . $result['HTTP_CODE']);
        }
    }

    //Put FulfillmentService
    public static function putFulfillmentService($user,$fulfillmentServiceId){

        $result = ShopifyAdminApi::request(
            $user,
            'PUT',
            '/admin/api/2020-10/fulfillment_services/'.$fulfillmentServiceId.'.json',
            json_encode(
                array(
                    "fulfillment_service" => array(
                        "id" => $fulfillmentServiceId,
                        "name" => "Greendropship"
                    )
                )
            )
        );


        if (isset($result['HTTP_CODE']) && $result['HTTP_CODE'] == 200) {

            $user->fulfillment_service_id = $result['body']['fulfillment_service']['id'];
            $user->fulfillment_location_id = $result['body']['fulfillment_service']['location_id'];
            $user->save();

            return array(
                'result' => true
            );
        } else {
            throw new Exception("Can't create a fulfillment service. -> HTTP_CODE: " . $result['HTTP_CODE']);
        }
    }


    //Get Order Informacion
    public static function getOrderInformation($user,$id_shopify){

       return ShopifyAdminApi::request(
            $user,
            'GET',
            '/admin/api/2021-01/orders/'.$id_shopify.'.json'
        );

    }

    //Get Inventory Item Id
    public static function getInventoryItemId($user,$id_variant){

       return ShopifyAdminApi::request(
            $user,
            'GET',
            '/admin/api/2021-01/variants/'.$id_variant.'.json'
        );

    }


    //Get Item Location Id
    public static function getItemLocationId($user,$inventory_item_id){

       return ShopifyAdminApi::request(
            $user,
            'GET',
            '/admin/api/2021-01/inventory_levels.json?inventory_item_ids='.$inventory_item_id
        );

    }


    //Get Fulfillment Services
    public static function getFulfillmentServices($user){

       return ShopifyAdminApi::request(
            $user,
            'GET',
            '/admin/api/2020-10/fulfillment_services.json'
        );

    }

    //Fulfill Order
    public static function fulfillItem($user,$location_id,$tracking_number,$line_item_id,$order_id,$shipping_carrier_code){

        $carrier = $shipping_carrier_code;

        if($shipping_carrier_code == 'shqusps1')$carrier = 'USPS';
        if($shipping_carrier_code == 'shqusps2')$carrier = 'USPS';
        if($shipping_carrier_code == 'shqfedex')$carrier = 'FedEx';


        $result = ShopifyAdminApi::request(
            $user,
            'POST',
            '/admin/api/2021-01/orders/'.$order_id.'/fulfillments.json',
            json_encode(
                array(
                    "fulfillment" => array(
                        "location_id" => $location_id,
                        "tracking_number" => $tracking_number,
                        "tracking_company" => $carrier,
                       // "tracking_company" => 'FedEx',
                        "line_items" => array(
                            0 => array(
                                "id" => $line_item_id
                            )
                        )
                    )
                )
            )
        );

       return $result;
    }

    //FulfilledOrder
    public static function fulfilledOrder($user,$order_id,$fulfillment_id){

       return ShopifyAdminApi::request(
            $user,
            'POST',
            '/admin/api/2021-01/orders/'.$order_id.'/fulfillments/'.$fulfillment_id.'/complete.json'
        );

    }

}
