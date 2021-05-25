<?php

namespace App\Libraries\Magento;

use App\OrderShippingAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Libraries\Shopify\ShopifyAdminApi;
use Illuminate\Support\Facades\Auth;
use App\User;

class MOrder
{
    //https://magento.stackexchange.com/questions/136028/magento-2-create-order-using-rest-api

    public static function getEstimatesShipping($order, $cartId)
    {
        $api = MagentoApi::getInstance();
        $shipping = [
            'address' => [
                'region' => $order->shipping_address->province,
                'regionId' => self::getSateIdByName($order->shipping_address->province),
                'regionCode' => $order->shipping_address->province_code,
                'countryId' => 'US',
                'street' => [$order->shipping_address->address1],
                'postcode' => $order->shipping_address->zip,
                'city' => $order->shipping_address->city,
                'firstname' => $order->shipping_address->first_name,
                'lastname' => $order->shipping_address->last_name,
                //'customer_id' => 1,
                'email' => $order->customer->email,
                'telephone' => "512-555-1177", //mandatory field;
                //'same_as_billing' => 1, //mandatory field;
                // 'cartId' => $cartId,
            ]
        ];

        $querymg = DB::connection('mysql_magento')->select('SELECT * FROM `mg_quote_id_mask` WHERE quote_id = ' . $cartId);
        $maskid = 0;
        if (count($querymg)) {
            $maskid = $querymg[0]->masked_id;
        }
        $result = $api->query('POST', 'guest-carts/' . $maskid . '/estimate-shipping-methods', [], json_encode($shipping));
        return $result;
    }

    public static function createCart($order, $order_model)
    {
        if ($order_model->magento_quote_id == null) {


            $api = MagentoApi::getInstance();
            $cartId = $api->query('POST', 'guest-carts');
            $products_added = [];
            $quote_id = 0;

            if (isset($cartId)) {
                foreach ($order->line_items as $product) {
                    $cartId = str_replace('"', "", $cartId);
                    $cartItem = [
                        'cartItem' => [
                            'quote_id' => $cartId,
                            'sku' => $product->sku,
                            'qty' => $product->quantity,
                        ]
                    ];

                    $carts = $api->query('POST', 'guest-carts/' . $cartId . '/items', [], json_encode($cartItem));
                    if (isset(json_decode($carts)->item_id) && json_decode($carts)->item_id > 0) {
                        $products_added[] = json_decode($carts)->item_id;
                        $quote_id = json_decode($carts)->quote_id;
                    } else {
                        log::error($carts);
                    }
                }
                $order_model->magento_quote_id = $quote_id;
                $order_model->save();

                if (!count($products_added)) {
                    return false;
                }
            }
        }

        return $order_model->magento_quote_id;
    }



    public static function getSateIdByName($name)
    {
        foreach (self::USAstates() as $key => $value) {
            if (strtolower($value) == strtolower($name)) return $key;
        }
        return 0;
    }

    public static function getSateCodeByName($name)
    {
        foreach (self::USACodestates() as $key => $value) {
            if (strtolower($value) == strtolower($name)) return $key;
        }
        return 0;
    }

    public static function getSateById($id)
    {
        foreach (self::USAstates() as $key => $value) {
            if ($key == $id) return $value;
        }
        return 0;
    }

    public static function USAstates()
    {
        return array(
            1 => 'Alabama',
            2 => 'Alaska',
            3 => 'American Samoa',
            4 => 'Arizona',
            5 => 'Arkansas',
            6 => 'Armed Forces Africa',
            7 => 'Armed Forces Americas',
            8 => 'Armed Forces Canada',
            9 => 'Armed Forces Europe',
            10 => 'Armed Forces Middle East',
            11 => 'Armed Forces Pacific',
            12 => 'California',
            13 => 'Colorado',
            14 => 'Connecticut',
            15 => 'Delaware',
            16 => 'District of Columbia',
            17 => 'Federated States Of Micronesia',
            18 => 'Florida',
            19 => 'Georgia',
            20 => 'Guam',
            21 => 'Hawaii',
            22 => 'Idaho',
            23 => 'Illinois',
            24 => 'Indiana',
            25 => 'Iowa',
            26 => 'Kansas',
            27 => 'Kentucky',
            28 => 'Louisiana',
            29 => 'Maine',
            30 => 'Marshall Islands',
            31 => 'Maryland',
            32 => 'Massachusetts',
            33 => 'Michigan',
            34 => 'Minnesota',
            35 => 'Mississippi',
            36 => 'Missouri',
            37 => 'Montana',
            38 => 'Nebraska',
            39 => 'Nevada',
            40 => 'New Hampshire',
            41 => 'New Jersey',
            42 => 'New Mexico',
            43 => 'New York',
            44 => 'North Carolina',
            45 => 'North Dakota',
            46 => 'Northern Mariana Islands',
            47 => 'Ohio',
            48 => 'Oklahoma',
            49 => 'Oregon',
            50 => 'Palau',
            51 => 'Pennsylvania',
            52 => 'Puerto Rico',
            53 => 'Rhode Island',
            54 => 'South Carolina',
            55 => 'South Dakota',
            56 => 'Tennessee',
            57 => 'Texas',
            58 => 'Utah',
            59 => 'Vermont',
            60 => 'Virgin Islands',
            61 => 'Virginia',
            62 => 'Washington',
            63 => 'West Virginia',
            64 => 'Wisconsin',
            65 => 'Wyoming',
        );
    }

    public static function USACodestates()
    {
        return array(
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AS' => 'American Samoa',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'AE' => 'Armed Forces Africa',
            'AA' => 'Armed Forces Americas',
            'AE' => 'Armed Forces Canada',
            'AE' => 'Armed Forces Europe',
            'AE' => 'Armed Forces Middle East',
            'AP' => 'Armed Forces Pacific',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District of Columbia',
            'FSM' => 'Federated States Of Micronesia',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'GU' => 'Guam',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MH' => 'Marshall Islands',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'MP' => 'Northern Mariana Islands',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PW' => 'Palau',
            'PA' => 'Pennsylvania',
            'PR' => 'Puerto Rico',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VI' => 'Virgin Islands',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'AWV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        );
    }

    //working
    public static function createOrderv2($order_model)
    {
        $api = MagentoApi::getInstance();


        //get shop information
        $merchant = User::find($order_model->id_customer);
        $res = ShopifyAdminApi::getShopInformation($merchant);
        $ad1 = $res['address1'];


        

        //Create shipping and billing address
        $shipping_address = OrderShippingAddress::where('id_order', $order_model->id)->first();
        $shipping = [
            'addressInformation' => [
                'shipping_address' => [
                    'region' => $shipping_address->province,
                    'region_id' => self::getSateIdByName($shipping_address->province),
                    'region_code' => $shipping_address->province_code,
                    'country_id' => 'US',
                    'street' => [$shipping_address->address1],
                    'postcode' => $shipping_address->zip,
                    'city' => $shipping_address->city,
                    'firstname' => $shipping_address->first_name,
                    'lastname' => $shipping_address->last_name,
                    'email' => $shipping_address->email,
                    'telephone' => "512-555-1112", //mandatory field;
                ],
                'billing_address' => [
                    'region' => $res['province'],
                    'region_id' => self::getSateIdByName($res['province']),
                    'region_code' => $res['province_code'],
                    'country_id' => 'US',
                    'street' => [$ad1],
                    'postcode' => $res['zip'],
                    'city' => $res['city'],
                    'firstname' => $res['shop_owner'],
                    'lastname' => $res['name'],
                    'email' => $res['email'],
                    'telephone' => "512-555-1115", //mandatory field;
                    //'telephone' => $res['phone'], //mandatory field;
                ],
                'shipping_carrier_code' => 'shqusps2',
                'shipping_method_code' => 'PriorityMail',
            ]
        ];



        $querymg = DB::connection('mysql_magento')->select('SELECT * FROM `mg_quote_id_mask` WHERE quote_id = ' . $order_model->magento_quote_id);
        $maskid = 0;
        if (count($querymg)) {

            $maskid = $querymg[0]->masked_id;
        }

        $paymentMethodsRes = $api->query('POST', 'guest-carts/' . $maskid . '/shipping-information', [], json_encode($shipping));
        if (!isset(json_decode($paymentMethodsRes)->payment_methods)) {
            return 'cannot get any payment method';
        }

        $paymentInformation = [
            'paymentMethod' => [
                'method' => 'checkmo',
            ],
            'shippingMethod' => [
                'method_code' => 'PriorityMail',
                'carrier_code' => 'shqusps2'
            ]
        ];


        $orderRes = $api->query('PUT', 'guest-carts/' . $maskid . '/order', [], json_encode($paymentInformation));

        if (isset($orderRes) && (int)str_replace('"', "", $orderRes) > 0) {
            $cart = $api->query('GET', 'carts/' . $order_model->magento_quote_id);
            if (isset(json_decode($cart)->reserved_order_id)) {
                $querymg = DB::connection('mysql_magento')->select('SELECT * FROM `mg_sales_order` WHERE quote_id = ' . $order_model->magento_quote_id);
                $entity = 0;
                if (count($querymg)) {
                    $entity = $querymg[0]->entity_id;
                }
                $order_model->magento_entity_id = $entity;
                $order_model->save();
                return array(
                    'quote_id' => json_decode($cart)->id . '',
                    'reserved_order_id' => json_decode($cart)->reserved_order_id,
                );
            }
        }

        return null;
    }

    public static function changeStatus($orderId)
    {
        $api = MagentoApi::getInstance();
        $querymg = DB::connection('mysql_magento')->select('SELECT * FROM `mg_sales_order` WHERE quote_id = ' . $orderId);
        $entity = 0;
        if (count($querymg)) {
            $entity = $querymg[0]->entity_id;
        }
        $payload = [
            'statusHistory' => [
                'comment' => 'cancel request',
                'createdAt' => date('Y-m-d H:i:s'),
                'entity_id' => $entity,
                'is_customer_notified' => 0,
                'is_visible_on_front' => 1,
                'status' => 'CancelRequest',

            ]
        ];

        $orderRes = $api->query('POST', 'orders/' . $entity . '/comments', [], json_encode($payload));

        return $orderRes;
    }
}
