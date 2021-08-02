<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DashboardSteps;
use App\ImportList;
use App\Libraries\OrderStatus;
use App\Libraries\Shopify\ShopifyAdminApi;
use App\MonthlyRecurringPlan;
use App\MonthlyRecurringPlanOrders;
use App\MyProducts;
use App\Settings;
use App\Order;
use App\User;
use App\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AjaxController extends Controller
{

    public function index(Request $parameters)
    {

        if ($parameters['action'] == 'add_check') {

            if ($row = DashboardSteps::find($parameters['id_user'])) {
                if ($parameters['step'] == 1) {
                    $row->step1 = $parameters['value'];
                }
                if ($parameters['step'] == 2) {
                    $row->step2 = $parameters['value'];
                }
                if ($parameters['step'] == 3) {
                    $row->step3 = $parameters['value'];
                }
                if ($parameters['step'] == 4) {
                    $row->step4 = $parameters['value'];
                }
                if ($parameters['step'] == 5) {
                    $row->step5 = $parameters['value'];
                }
                if ($parameters['step'] == 6) {
                    $row->step6 = $parameters['value'];
                }
                $row->save();
            } else {
                $row = new DashboardSteps;
                $row->id = $parameters['id_user'];
                $row->step1 = 0;
                $row->step2 = 0;
                $row->step3 = 0;
                $row->step4 = 0;
                $row->step5 = 0;
                $row->step6 = 0;
                $row->save();

                $row = DashboardSteps::find($parameters['id_user']);
                if ($parameters['step'] == 1) {
                    $row->step1 = $parameters['value'];
                }
                if ($parameters['step'] == 2) {
                    $row->step2 = $parameters['value'];
                }
                if ($parameters['step'] == 3) {
                    $row->step3 = $parameters['value'];
                }
                if ($parameters['step'] == 4) {
                    $row->step4 = $parameters['value'];
                }
                if ($parameters['step'] == 5) {
                    $row->step5 = $parameters['value'];
                }
                if ($parameters['step'] == 6) {
                    $row->step6 = $parameters['value'];
                }
                $row->save();
            }

            echo json_encode(1);
        }

        if ($parameters['action'] == 'add_import_list') {
            $product = Products::where('sku', $parameters['sku'])->first();
            if ($product != null) {
                $import_product = ImportList::where('id_customer', Auth::User()->id)
                    ->where('id_product', $product->id)->first();
                if ($import_product == null) {
                    $row = new ImportList;
                    $row->id_customer = Auth::user()->id;
                    $row->id_product = $product->id;
                    $row->save();
                }
                return json_encode([
                    'result' => true,
                    'sku' => $parameters['sku']
                ]);
            } else {
                return json_encode([
                    'result' => false
                ]);
            }
        }

        if ($parameters['action'] == 'delete_import_list') {

            $this->authorize('plan_delete-product-import-list');
            $row = ImportList::whereIn('id', $parameters['id_import_list']);
            $row->delete();

            return json_encode(1);
        }

        if ($parameters['action'] == 'update_notes') {
            $row = Order::find($parameters['id_order']);
            $row->notes = $row->notes . $parameters['notes'];
            $row->save();

            return json_encode(1);
        }

        if ($parameters['action'] == 'update-user') {
            $user = User::where('id', '!=', Auth::user()->id)
                ->where('email', $parameters['email'])->first();
            if ($user == null) {
                User::find(Auth::user()->id)->update([
                    'name' => $parameters['name'],
                    'email' => $parameters['email'],
                    'password' => Hash::make($parameters['password'])
                ]);
                return json_encode(['result' => true]);
            } else {
                return json_encode(['result' => false]);
            }
        }

        if ($parameters['action'] == 'create-user') {
            $user = User::where('email', $parameters['email'])->first();
            if ($user == null) {
                $row = new User;
                $row->name = $parameters['name'];
                $row->email = $parameters['email'];
                $row->password = Hash::make($parameters['password']);
                $row->role = 'admin';
                $row->save();
                return json_encode($row);
            } else {
                return json_encode(['result' => false]);
            }
        }

        if ($parameters['action'] == 'admin-users') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $users = User::where('role', 'admin');
            if ($parameters['name'] != '') {
                $users = $users->where('name', 'like', '%' . $parameters['name'] . '%');
            }
            if ($parameters['email'] != '') {
                $users = $users->where('email', 'like', '%' . $parameters['email'] . '%');
            }
            if ($parameters['active'] != '') {
                $users = $users->where('active', $parameters['active']);
            }
            $total_count = $users->count();
            $users = $users->orderBy('users.id', 'asc')
                ->skip(($page_number - 1) * $page_size)
                ->take($page_size)->get();
            return json_encode([
                'users' => $users,
                'total_count' => $total_count,
                'page_size' => $page_size,
                'page_number' => $page_number
            ]);
        }

        if ($parameters['action'] == 'admin-user-name') {
            $names = DB::table('users')
                ->where('role', 'admin')
                ->where('name', 'like', '%' . $parameters['name'] . '%')
                ->orderBy('name')->pluck('name');
            return json_encode(['names' => $names]);
        }

        if ($parameters['action'] == 'admin-user-email') {
            $emails = DB::table('users')
                ->where('role', 'admin')
                ->where('email', 'like', '%' . $parameters['email'] . '%')
                ->orderBy('email')->pluck('email');
            return json_encode(['emails' => $emails]);
        }

        if ($parameters['action'] == 'admin-change-password') {
            $old_password = json_decode($parameters['old_password']);
            $new_password = json_decode($parameters['new_password']);
            $password = User::find(Auth::user()->id)->password;
            if (Hash::check($old_password, $password)) {
                User::find(Auth::user()->id)->update(['password' => Hash::make($new_password)]);
                return json_encode(['result' => true]);
            } else {
                return json_encode(['result' => false]);
            }
        }

        if ($parameters['action'] == 'my-products') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $prods = Products::select('products.*', 'my_products.id_imp_product as id_my_product', 'my_products.id_shopify', 'my_products.id as id_my_products', 'my_products.profit')
                ->join('import_list', 'import_list.id_product', '=', 'products.id')
                ->join('my_products', 'my_products.id_imp_product', '=', 'import_list.id')
                ->where('my_products.id_customer', Auth::user()->id)->whereNull('my_products.deleted_at')->orderBy('my_products.id', 'desc')->skip(($page_number - 1) * $page_size)->take($page_size)->get();
            $total_count = MyProducts::count();
            $search = new SearchController;
            foreach ($prods as $product) {
                $product['brand'] = $search->getAttributeByCode($product, 'brand');
                if ($product->images != null && count(json_decode($product->images)) > 0) {
                    $product->image_url_75 = env('URL_MAGENTO_IMAGES') . '/dc09e1c71e492175f875827bcbf6a37c' . json_decode($product->images)[0]->file;
                    $product->image_url_285 = env('URL_MAGENTO_IMAGES') . '/e793809b0880f758cc547e70c93ae203' . json_decode($product->images)[0]->file;
                } else {
                    $product->image_url_75 = env('URL_MAGENTO_IMAGES') . '/dc09e1c71e492175f875827bcbf6a37cno_selection';
                    $product->image_url_285 = env('URL_MAGENTO_IMAGES') . '/e793809b0880f758cc547e70c93ae203no_selection';
                }
            }
            return json_encode([
                'prods' => $prods,
                'total_count' => $total_count,
                'page_size' => $page_size,
                'page_number' => $page_number
            ]);
        }

        if ($parameters['action'] == 'import-list') {
            $this->authorize('plan_delete-product-import-list');
            $this->authorize('plan_view-my-products');
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $prods = Products::select('products.*', 'import_list.id as id_import_list')
                ->join('import_list', 'import_list.id_product', '=', 'products.id')
                ->whereNotIn('import_list.id', MyProducts::where('id_customer', Auth::User()->id)->pluck('id_imp_product'))
                ->where('import_list.id_customer', Auth::user()->id)->orderBy('import_list.updated_at', 'desc');

            $total_count = $prods->count();
            $prods = $prods->skip(($page_number - 1) * $page_size)->take($page_size)->get();
            foreach ($prods as $product) {
                if ($product['images'] != null && count(json_decode($product['images'])) > 0) {
                    $product['image_url'] = env('URL_MAGENTO_IMAGES') . '/e793809b0880f758cc547e70c93ae203' . json_decode($product['images'])[0]->file;
                    $product['delete_image_url'] = env('URL_MAGENTO_IMAGES') . '/dc09e1c71e492175f875827bcbf6a37c' . json_decode($product['images'])[0]->file;
                } else {
                    $product['image_url'] = env('URL_MAGENTO_IMAGES') . '/e793809b0880f758cc547e70c93ae203no_selection';
                    $product['delete_image_url'] = env('URL_MAGENTO_IMAGES') . '/dc09e1c71e492175f875827bcbf6a37cno_selection';
                }

                $search = new SearchController;
                $images = [];
                foreach (json_decode($product['images']) as $image) {
                    $images[] = env('URL_MAGENTO_IMAGES') . '/3a98496dd7cb0c8b28c4c254a98f915a' . $image->file;
                }
                $product['description'] = $search->getAttributeByCode($product, 'description');
                $product['size'] = $search->getAttributeByCode($product, 'size');
                $product['images'] = $images;
                $product['ship_height'] = round($search->getAttributeByCode($product, 'ship_height'), 2);
                $product['ship_width'] = round($search->getAttributeByCode($product, 'ship_width'), 2);
                $product['ship_length'] = round($search->getAttributeByCode($product, 'ship_length'), 2);
            }
            $settings = Settings::where('id_merchant', Auth::user()->id)->first();
            if ($settings == null) {
                $settings = new Settings();
                $settings->set8 = 0;
            }
            return json_encode([
                'improds' => [
                    'products' => $prods,
                    'profit' => $settings->set8,
                    'plan' => Auth::User()->plan,
                    'shopify_url' => Auth::User()->shopify_url
                ],
                'total_count' => $total_count,
                'page_size' => $page_size,
                'page_number' => $page_number
            ]);
        }

        if ($parameters['action'] == 'migration-count') {
            $count = ShopifyAdminApi::countProducts(Auth::user());
            return json_encode([
                'action' => 'migration',
                'total_count' => $count['body']['count'],
                'index' => 0,
                'location_id' => 0,
                'count' => 0
            ]);
        }

        if ($parameters['action'] == 'migration') {
            $rows = [];
            $products = ShopifyAdminApi::getProducts(Auth::User(), $parameters['index']);
            if ($parameters['index'] == 0 && count($products['body']['products'])) {
                $location_id = ShopifyAdminApi::getItemLocationId(Auth::User(), $products['body']['products'][0]['variants'][0]['inventory_item_id']);
                $parameters['location_id'] = $location_id['body']['inventory_levels'][0]['location_id'];
            }
            $parameters['index'] = $products['body']['products'][count($products['body']['products']) - 1]['id'];
            foreach ($products['body']['products'] as $product) {
                if (substr($product['variants'][0]['sku'], 0, 2) == 'KH') {
                    $rows[] = [
                        'sku' => $product['variants'][0]['sku'],
                        'price' => $product['variants'][0]['price'],
                        'id_shopify' => $product['id'],
                        'id_variant_shopify' => $product['variants'][0]['id'],
                        'inventory_item_id_shopify' => $product['variants'][0]['inventory_item_id'],
                        'location_id_shopify' => $parameters['location_id'],
                        'user_id' => Auth::User()->id,
                        'payload' => json_encode([
                            'name' => $product['title'],
                            'image_url' => $product['image'] != null ? $product['image']['src'] : ''
                        ]),
                        'type' => Products::where('sku', $product['variants'][0]['sku'])->first() == null ? 'delete' : 'migration'
                    ];
                }
            }
            DB::table('temp_migrate_products')->insert($rows);
            $parameters['count'] = $parameters['count'] + count($products['body']['products']);
            if ($parameters['count'] == $parameters['total_count']) {
                $mig_products = DB::table('temp_migrate_products')
                    ->select('temp_migrate_products.*', 'products.price as cost')
                    ->leftJoin('products', 'temp_migrate_products.sku', '=', 'products.sku')
                    ->where('user_id', Auth::user()->id)->orderByDesc('id_shopify');
                $total_count = $mig_products->count();
                $mig_products = $mig_products->skip(0)->take(10)->get();
                return json_encode([
                    'mig_products' => $mig_products,
                    'total_count' => $total_count,
                    'count' => $parameters['count'],
                    'page_number' => 1,
                    'page_size' => 10
                ]);
            } else {
                return json_encode([
                    'action' => 'migration',
                    'index' => $parameters['index'],
                    'location_id' => $parameters['location_id'],
                    'total_count' => $parameters['total_count'],
                    'count' => $parameters['count'],
                ]);
            }
        }

        if ($parameters['action'] == 'migrate-products') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $mig_products = DB::table('temp_migrate_products')
                ->select('temp_migrate_products.*', 'products.price as cost')
                ->leftJoin('products', 'temp_migrate_products.sku', '=', 'products.sku')
                ->where('user_id', Auth::user()->id)->orderByDesc('id_shopify');
            $total_count = $mig_products->count();
            $mig_products = $mig_products->skip(($page_number - 1) * $page_size)->take($page_size)->get();
            return json_encode([
                'mig_products' => $mig_products,
                'page_size' => $page_size,
                'page_number' => $page_number,
                'total_count' => $total_count
            ]);
        }

        if ($parameters['action'] == 'set-default-profit') {
            $mig_skus = DB::table('temp_migrate_products')
                ->where('user_id', Auth::User()->id)
                ->where('type', 'migration')->pluck('sku');
            foreach ($mig_skus as $sku) {
                $cost = DB::table('products')->where('sku', $sku)->pluck('price')->first();
                $profit = Settings::where('id_merchant', Auth::user()->id)->first()->set8;
                DB::table('temp_migrate_products')
                    ->where('user_id', Auth::User()->id)
                    ->where('sku', $sku)
                    ->update(['price' => $cost * (100 + $profit) / 100]);
            }
            return json_encode(['result' => true]);
        }

        if ($parameters['action'] == 'change-profit') {
            $cost = DB::table('products')->where('sku', $parameters['sku'])->pluck('price')->first();
            DB::table('temp_migrate_products')->where('user_id', Auth::User()->id)
                ->where('sku', $parameters['sku'])
                ->update(['price' => $cost * (100 + $parameters['profit']) / 100]);
            return json_encode(['result' => true]);
        }

        if ($parameters['action'] == 'product_collection') {
            $collections = DB::table('user_collections_tags_types')
                ->where([['user_id', Auth::User()->id], ['type', 'C']])
                ->where('value', 'like', '%' . json_decode($parameters['collection']) . '%')
                ->orderBy('value')->pluck('value');
            return json_encode(['collections' => $collections]);
        }

        if ($parameters['action'] == 'product_type') {
            $types = DB::table('user_collections_tags_types')
                ->where([['user_id', Auth::User()->id], ['type', 'X']])
                ->where('value', 'like', '%' . $parameters['type'] . '%')
                ->orderBy('value')->pluck('value');
            return json_encode(['types' => $types]);
        }

        if ($parameters['action'] == 'product_tag') {
            $tags = DB::table('user_collections_tags_types')
                ->where([['user_id', Auth::User()->id], ['type', 'T']])
                ->where('value', 'like', '%' . $parameters['tag'] . '%')
                ->orderBy('value')->pluck('value');
            return json_encode(['tags' => $tags]);
        }

        if ($parameters['action'] == 'admin-order-number') {
            $numbers = DB::table('orders')->distinct()
                ->where('magento_order_id', 'like', '%' . $parameters['number'] . '%')
                ->orderBy('magento_order_id')
                ->pluck('magento_order_id');
            return json_encode(['numbers' => $numbers]);
        }

        if ($parameters['action'] == 'admin-order-merchant') {
            $names = DB::table('users')
                ->where('name', 'like', '%' . $parameters['name'] . '%')
                ->orderBy('name')
                ->pluck('name');
            return json_encode(['names' => $names]);
        }

        if ($parameters['action'] == 'admin-orders') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $order_list = Order::select('orders.*', 'st1.name as status1', 'st1.color as color1', 'st2.name as status2', 'st2.color as color2', 'us.name as merchant_name')
                ->join('order_shipping_address as osa', 'orders.id', 'osa.id_order')
                ->join('status as st1', 'st1.id', 'orders.financial_status')
                ->join('status as st2', 'st2.id', 'orders.fulfillment_status')
                ->join('users as us', 'us.id', 'orders.id_customer');
            if ($parameters['order_number'] != '') {
                $order_list = $order_list->where('magento_order_id', 'like', '%' . $parameters['order_number'] . '%');
            }

            if ($parameters['from'] != '') {
                $order_list = $order_list->whereDate('orders.created_at', '>=', $parameters['from']);
            }

            if ($parameters['to'] != '') {
                $order_list = $order_list->whereDate('orders.created_at', '<=', $parameters['to']);
            }

            if ($parameters['payment_status'] > 0) {
                $order_list = $order_list->where('orders.financial_status', $parameters['payment_status']);
            }

            if ($parameters['order_state'] > 0) {
                $order_list = $order_list->where('orders.fulfillment_status', $parameters['order_state']);
            }

            if ($parameters['merchant_name'] != '') {
                $order_list = $order_list->where('us.name', 'like', '%' . $parameters['merchant_name'] . '%');
            }

            $total_count = $order_list->count();
            $order_list = $order_list->orderBy('orders.updated_at', 'desc')->skip(($page_number - 1) * $page_size)->take($page_size)->get();
            return json_encode([
                'order_list' => $order_list,
                'page_number' => $page_number,
                'page_size' => $page_size,
                'total_count' => $total_count
            ]);
        }

        if ($parameters['action'] == 'change-user-status') {
            DB::table('users')
                ->where('id', $parameters['user_id'])
                ->update(['active' => $parameters['active']]);
            return json_encode(['result' => true]);
        }

        if ($parameters['action'] == 'admin-merchants') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $merchants_list = User::select('users.*')->where('role', 'merchant');
            if ($parameters['name'] != '') {
                $merchants_list = $merchants_list->where('name', 'like', '%' . $parameters['name'] . '%');
            }

            if ($parameters['email'] != '') {
                $merchants_list = $merchants_list->where('email', 'like', '%' . $parameters['email'] . '%');
            }

            if ($parameters['url'] != '') {
                $merchants_list = $merchants_list->where('shopify_url', 'like', '%' . $parameters['url'] . '%');
            }

            if ($parameters['plan'] != '') {
                if ($parameters['plan'] == '0') {
                    $merchants_list = $merchants_list->whereNull('plan');
                } else {
                    $merchants_list = $merchants_list->where('plan', $parameters['plan']);
                }
            }

            if ($parameters['active'] != '') {
                $merchants_list = $merchants_list->where('active', $parameters['active']);
            }
            $total_count = $merchants_list->count();
            $merchants_list = $merchants_list->skip(($page_number - 1) * $page_size)->take($page_size)->get();
            return json_encode([
                'merchants' => $merchants_list,
                'page_number' => $page_number,
                'page_size' => $page_size,
                'total_count' => $total_count
            ]);
        }

        if ($parameters['action'] == 'admin-merchant-name') {
            $names = DB::table('users')
                ->where('role', 'merchant')
                ->where('name', 'like', '%' . $parameters['name'] . '%')
                ->orderBy('name')->pluck('name');
            return json_encode(['names' => $names]);
        }

        if ($parameters['action'] == 'admin-merchant-email') {
            $emails = DB::table('users')
                ->where('role', 'merchant')
                ->where('email', 'like', '%' . $parameters['email'] . '%')
                ->orderBy('email')->pluck('email');
            return json_encode(['emails' => $emails]);
        }

        if ($parameters['action'] == 'admin-merchant-url') {
            $urls = DB::table('users')
                ->where('role', 'merchant')
                ->where('shopify_url', 'like', '%' . $parameters['shopify_url'] . '%')
                ->orderBy('shopify_url')->pluck('shopify_url');
            return json_encode(['urls' => $urls]);
        }

        if ($parameters['action'] == 'my-orders') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $order_list = Order::select(
                'orders.*',
                'osa.first_name',
                'osa.last_name',
                'st1.name as status1',
                'st1.color as color1',
                'st2.name as status2',
                'st2.color as color2',
                'st1.id as financial_status',
                'st2.id as fulfillment_status'
            )
                ->join('order_shipping_address as osa', 'orders.id', 'osa.id_order')
                ->join('status as st1', 'st1.id', 'orders.financial_status')
                ->join('status as st2', 'st2.id', 'orders.fulfillment_status')
                ->where('orders.id_customer', Auth::user()->id);

            $total_period_orders = Order::where('id_customer', Auth::user()->id);

            if ($parameters['notifications'] != '' && $parameters['notifications']) {
                $order_list = $order_list->where('financial_status', OrderStatus::Outstanding)
                    ->where('fulfillment_status', OrderStatus::NewOrder);
                $total_period_orders = $total_period_orders->where('financial_status', OrderStatus::Outstanding)
                    ->where('fulfillment_status', OrderStatus::NewOrder);
            }

            $order_count = $order_list->count();

            $current_period = MonthlyRecurringPlan::where('current', 1)->where('merchant_id', Auth::user()->id)->first();

            if ($parameters['from'] != '' && $parameters['to'] != '') {
                $total_period_orders = $total_period_orders->whereDate('created_at', '>=', $parameters['from'])
                    ->whereDate('created_at', '<=', $parameters['to']);
            } else {
                $total_period_orders = $total_period_orders->whereDate('created_at', '>=', $current_period->start_date)
                    ->whereDate('created_at', '<=', $current_period->end_date);
            }

            if ($parameters['order_number'] != '' && $parameters['order_number'] > 0) {
                $order_list = $order_list->where('order_number_shopify', '#' . $parameters['order_number']);
                $total_period_orders = $total_period_orders->where('order_number_shopify', '#' . $parameters['order_number']);
            } else {
                if ($parameters['from'] != '' && $parameters['to'] != '') {
                    $order_list = $order_list->whereDate('created_at', '>=', $parameters['from'])
                        ->whereDate('created_at', '<=', $parameters['to']);
                }
            }

            $basic_period = '';
            if (Auth::user()->plan == 'basic' && $current_period != null) {
                $basic_period = $current_period->start_date . ' - ' . $current_period->end_date;
            }

            if ($parameters['payment_status'] > 0) {
                $order_list = $order_list->where('orders.financial_status', $parameters['payment_status']);
                $total_period_orders = $total_period_orders->where('orders.financial_status', $parameters['payment_status']);
            }

            if ($parameters['order_state'] > 0) {
                $order_list = $order_list->where('orders.fulfillment_status', $parameters['order_state']);
                $total_period_orders = $total_period_orders->where('orders.fulfillment_status', $parameters['order_state']);
            }

            $notifications = Order::where('financial_status', OrderStatus::Outstanding)
                ->where('fulfillment_status', OrderStatus::NewOrder)
                ->where('orders.id_customer', Auth::user()->id)
                ->count();

            $total_period_orders = $total_period_orders->count();
            $total_count = $order_list->count();

            return json_encode([
                'my_orders' => [
                    'orders' => $order_list->orderBy('orders.id', 'desc')->skip(($page_number - 1) * $page_size)->take($page_size)->get(),
                    'notifications' => $notifications,
                    'from' => $parameters['from'],
                    'to' => $parameters['to'],
                    'basic_period' => $basic_period,
                    'total_period_orders' => $total_period_orders,
                    'total_count' => $order_count
                ],
                'total_count' => $total_count,
                'page_size' => $page_size,
                'page_number' => $page_number,
            ]);
        }
    }

    public function import(Request $request)
    {
        $rows = [];
        $skus = [];
        foreach ($request->skus as $sku) {
            $product = Products::where('sku', $sku)->first();
            if ($product != null) {
                $import_product = ImportList::where('id_customer', Auth::User()->id)->where('id_product', $product->id)->first();
                if ($import_product == null) {
                    $rows[] = [
                        'id_customer' => Auth::User()->id,
                        'id_product' => $product->id
                    ];
                    $skus[] = $sku;
                }
            }
        }
        DB::table('import_list')->insert($rows);
        return json_encode($skus);
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'set8' => 'required|integer',
        ]);

        $settings = Settings::where('id_merchant', Auth::user()->id)->first();
        if ($settings == null) {
            $settings = new Settings();
            $settings->id_merchant = Auth::user()->id;
        }
        $settings->set1 = $request->set1 == 'true' ? 1 : 0;
        $settings->set2 = $request->set2 == 'true' ? 1 : 0;
        $settings->set3 = $request->set3 == 'true' ? 1 : 0;
        $settings->set4 = $request->set4 == 'true' ? 1 : 0;
        $settings->set5 = $request->set5 == 'true' ? 1 : 0;
        $settings->set6 = $request->set6 == 'true' ? 1 : 0;
        $settings->set7 = $request->set7 == 'true' ? 1 : 0;
        $settings->set8 = $request->set8;
        $settings->save();
        return response()->json(['res' => 'ok']);
    }
}
