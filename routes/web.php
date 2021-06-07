<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Libraries\Magento\MagentoApi;
use App\Libraries\Magento\MOrder;
use App\Libraries\Magento\MProduct;
use App\Libraries\Shopify\ShopifyAdminApi;
use App\Order;
use App\Products;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

Auth::routes();
Route::get('/', 'HomeController@index')->name('home');
Route::get('/install', 'CallBackController@index');
Route::get('/callback', 'CallBackController@callback');
//Route::get('/callback', 'CallBackController@callback');

Route::group(['prefix' => 'products'], function () {
    Route::get('/', 'ProductListController@index');
});

Route::get('/ajax', 'AjaxController@index');

/*Products*/
Route::get('/search-products', 'SearchController@index');
Route::get('/search-products/{products}', 'SearchController@show');
Route::get('/import-list', 'ImportListController@index');
Route::get('/my-products', 'MyProductsController@index');
Route::post('/delete-shopify-product', 'MyProductsController@deleteProduct');
Route::post('/delete-all-shopify-product', 'MyProductsController@deleteAllProduct');
Route::post('/check-delete-shopify-products', 'MyProductsController@checkDeleteProducts');
Route::get('/migrate-products', 'MigrateProductsController@index');
Route::post('/delete-migrate-product', 'MigrateProductsController@deleteMigrateProduct');
Route::post('/delete-migrate-products', 'MigrateProductsController@deleteMigrateProducts');
Route::post('/check-delete-migrate', 'MigrateProductsController@checkDeleteMigrateProducts');
Route::post('/confirm-migrate-products', 'MigrateProductsController@confirmMigrateProducts');

/*Orders*/
Route::get('orders/exports', 'OrdersController@exportCSV');
Route::get('/orders', 'OrdersController@index');
Route::get('/orders/{orders}', 'OrdersController@show');
Route::get('/settings', 'SettingsController@index');
Route::get('/plans', 'PlansController@index');
Route::get('/help', 'HelpController@index');
Route::get('/orders/cancel/{order}', 'OrdersController@cancel');
Route::post('/save-address', 'OrdersController@saveAddress');
Route::get('/orders/cancel-request/{order}', 'OrdersController@cancelRequest');


/*Upgrade Plan*/
Route::post('/plans/save-token', 'PlansController@saveToken');
Route::post('/plans/update', 'PlansController@updatePlan');
Route::get('/plans/update-success', 'PlansController@updatePlanSuccess');
Route::get('/plans/update-failure', 'PlansController@updatePlanFailure');
Route::get('/plans/update-plan', 'PlansController@updatePlanUpdate');


//sync
Route::get('/sync-magento', 'SyncController@index');
Route::get('/sync-magento/categories', 'SyncController@syncCategories');
Route::get('/sync-magento/products', 'SyncController@syncProducts');
Route::get('/sync-magento/stock', 'SyncController@syncStock');
Route::post('/sync-magento/sync-shopify-stock', 'SyncController@syncShopifyStock');
Route::get('/sync-magento/wp', 'SyncController@syncWP');
Route::get('/sync-magento/arreglosku', 'SyncController@arregloSku');
Route::get('/sync-magento/tracking-number', 'SyncController@setTrackingNumber');
Route::get('/sync-magento/update-status-when-canceling', 'SyncController@updateStatusWhenCancelingMagento');
Route::get('/sync-magento/products-to-send', 'SyncController@productsToSend');
Route::get('/sync/log', 'SyncController@log');
Route::get('/sync-magento/shopifyupgraded', 'SyncController@shopifyupgraded');

/* Shopify*/
Route::post('/publish-product', 'ImportListController@publishShopify');
Route::post('/check-publish-products', 'ImportListController@checkPublishProducts');
Route::post('/publish-all-products', 'ImportListController@publishAllShopify');
Route::post('/create-order-webkook', 'ShopifyWebHooksController@createOrder');
Route::post('/customer-data-request-webhook', 'ShopifyWebHooksController@customerDataRequest');
Route::post('/customer-data-erasure-webhook', 'ShopifyWebHooksController@customerDataErasure');
Route::post('/shop-data-erasure-webhook', 'ShopifyWebHooksController@shopDataErasure');


/* STRIPE */
Route::post('/create-checkout-session', 'StripeController@createCheckoutSession');
Route::post('/check-payment-success', 'StripeWebHooksController@checkPaymentSuccess');
Route::post('/check-payment-fail', 'StripeWebHooksController@checkPaymentFail');
Route::get('/stripe-test/{payment_intent}', 'StripeWebHooksController@test');
Route::post('/create-cart-magento', 'OrdersController@createCart');


/*Run queue */
Route::get('/run-schedule', 'ShopifyWebHooksController@runschedule');

/*settings */

Route::post('/save-settings', 'AjaxController@saveSettings');


/* admin */


Route::prefix('admin')->group(function () {
    Route::get('orders/exports', 'AdminOrdersController@exportCSV');
    Route::get('dashboard', 'AdminDashboardController@index');
    Route::get('merchants', 'AdminMerchantsController@index');
    Route::get('merchants/exportCSV', 'AdminMerchantsController@exportCSV');
    Route::get('merchants/changeStatus/{merchant}/{status}', 'AdminMerchantsController@changeStatus');
    Route::get('merchants/show/{merchant}', 'AdminMerchantsController@show');
    Route::get('users', 'AdminUsersController@index');
    Route::get('orders', 'AdminOrdersController@index');
    Route::get('/orders/{orders}', 'AdminOrdersController@show');
    Route::post('/stats-data', 'AdminDashboardController@getData');
    Route::get('/orders/cancel/{order}', 'AdminOrdersController@cancel');
    Route::get('/logs', 'AdminDashboardController@logs');
    Route::get('/logs/download', 'AdminDashboardController@downloadLog');

});

//TEST
Route::get('/test', function () {

    $name = 'collection1,collection2,collection3,collection4';

    $collections_split = explode(',', $name);
    $collections = ShopifyAdminApi::getCollections(Auth::user());

    foreach ($collections_split as $item) {
        $filtered = $collections->first(function ($value) use ($item) {
            return trim($item) != '' && $value['name'] == trim($item);
        });

        if ($filtered == null) { //no existe
            $collectionId = ShopifyAdminApi::createCustomCollection(Auth::user(), $item);
            if ($collectionId != 0) {
                $result = ShopifyAdminApi::addProductToCustomCollection(Auth::user(),'5848129798306',$collectionId);
            }
        }
    }


    //$inventory =collect(DB::connection('mysql_magento')->select('SELECT * FROM `mg_inventory_stock_1`'))->where('is_salable',1);
    dd($collections);
    //return 0;
    /*
    foreach(Order::get() as $order){
        $data = json_decode($order->data);
        dd($data);
    }
    foreach (Products::get() as $product) {
        $stock = MProduct::getStock($product->sku);
        $stock = json_decode($stock);
        $stock_item = [
            'stock_item' => [
                'item_id' => $stock->item_id,
                'qty' => 200,
                'is_in_stock' => true,
            ]
        ];;
        $api = MagentoApi::getInstance();
        $stockid = $api->query('PUT', 'products/'. $product->sku . '/stockItems/' . $stock->item_id, [], json_encode($stock_item));
        var_dump($stockid);
        echo '<br>';
    }*/
    return 'finished';
});
