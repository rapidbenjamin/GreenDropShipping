<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DashboardSteps;
use App\ImportList;
use App\Libraries\Shopify\ShopifyAdminApi;
use App\MyProducts;
use App\Settings;
use App\Order;
use App\User;
use App\Products;
use Illuminate\Support\Facades\DB;

class AjaxController extends Controller
{

    public function index(Request $parameters)
    {

        if ($parameters['action'] == 'add_check') {

            if ($row = DashboardSteps::find($parameters['id_user'])) {
                if ($parameters['step'] == 1) $row->step1 = $parameters['value'];
                if ($parameters['step'] == 2) $row->step2 = $parameters['value'];
                if ($parameters['step'] == 3) $row->step3 = $parameters['value'];
                if ($parameters['step'] == 4) $row->step4 = $parameters['value'];
                if ($parameters['step'] == 5) $row->step5 = $parameters['value'];
                if ($parameters['step'] == 6) $row->step6 = $parameters['value'];
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
                if ($parameters['step'] == 1) $row->step1 = $parameters['value'];
                if ($parameters['step'] == 2) $row->step2 = $parameters['value'];
                if ($parameters['step'] == 3) $row->step3 = $parameters['value'];
                if ($parameters['step'] == 4) $row->step4 = $parameters['value'];
                if ($parameters['step'] == 5) $row->step5 = $parameters['value'];
                if ($parameters['step'] == 6) $row->step6 = $parameters['value'];
                $row->save();
            }

            echo json_encode(1);
        }

        if ($parameters['action'] == 'add_import_list') {
            $product = ImportList::where('id_customer', Auth::User()->id)->where('id_product', $parameters['id_product'])->first();
            if ($product == null) {
                $row = new ImportList;
                $row->id_customer = Auth::user()->id;
                $row->id_product = $parameters['id_product'];
                $row->save();
            }
            return json_encode($parameters['id_product']);
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


        if ($parameters['action'] == 'save-user') {
            $row = new User;
            $row->name = $parameters['user'];
            $row->email = $parameters['email'];
            $row->password = $parameters['password'];
            $row->role = 'admin';
            $row->save();

            return json_encode(1);
        }

        if ($parameters['action'] == 'my-products') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $prods = Products::select('products.*', 'my_products.id_imp_product as id_my_product','my_products.id_shopify','my_products.id as id_my_products','my_products.profit')
                ->join('import_list', 'import_list.id_product', '=', 'products.id')
                ->join('my_products', 'my_products.id_imp_product', '=', 'import_list.id')
                ->where('my_products.id_customer', Auth::user()->id)->whereNull('my_products.deleted_at')->orderBy('my_products.id', 'desc')->skip(($page_number - 1) * $page_size)->take($page_size)->get();
            $total_count = MyProducts::count();
            $search = new SearchController;
            foreach ($prods as $product) {
                $product['brand'] = $search->getAttributeByCode($product, 'brand');
                if ($product->images != null && count(json_decode($product->images)) > 0)
                    $product->image_url_75 = env('URL_MAGENTO_IMAGES'). '/dc09e1c71e492175f875827bcbf6a37c' . json_decode($product->images)[0]->file;
                    $product->image_url_285 = env('URL_MAGENTO_IMAGES'). '/e793809b0880f758cc547e70c93ae203' . json_decode($product->images)[0]->file;
            }
            return json_encode([
                'prods' => $prods,
                'total_count' => $total_count,
                'page_size' => $page_size,
                'page_number' => $page_number
            ]);
        }

        if($parameters['action'] == 'delete-import-list') {
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
                if ($product['images'] != null && count(json_decode($product['images'])) > 0)
                    $product['image_url'] = env('URL_MAGENTO_IMAGES'). '/e793809b0880f758cc547e70c93ae203' . json_decode($product['images'])[0]->file;

                $search = new SearchController;
                $images = [];
                foreach (json_decode($product['images']) as $image) {
                    $images[] = env('URL_MAGENTO_IMAGES'). '/3a98496dd7cb0c8b28c4c254a98f915a' . $image->file;
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

        if ($parameters['action'] == 'migration') {
            $page_size = $parameters['page_size'];
            $page_number = $parameters['page_number'];
            $products = ShopifyAdminApi::getProducts(Auth::User());
            $location_id = 0;
            if (count($products['body']['products'])) {
                $location_id = ShopifyAdminApi::getItemLocationId(Auth::User(), $products['body']['products'][0]['variants'][0]['inventory_item_id']);
            }
            return json_encode([
                'products' => count($products['body']['products']),
                'location_id' => $location_id['body']['inventory_levels'][0]['location_id'],
            ]);
        }

        if($parameters['action'] == 'migrating-products') {
            $products = ShopifyAdminApi::getProducts(Auth::User());
            $index = $parameters['index'];
            $location_id = $parameters['location_id'];
            $product = $products['body']['products'][$index];
            if(substr($product['variants'][0]['sku'], 0, 2) == 'KH') {
                $row = [
                    'sku' => $product['variants'][0]['sku'],
                    'price' => $product['variants'][0]['price'],
                    'id_shopify' => $product['id'],
                    'id_variant_shopify' => $product['variants'][0]['id'],
                    'inventory_item_id_shopify' => $product['variants'][0]['inventory_item_id'],
                    'location_id_shopify' => $location_id,
                    'user_id' => Auth::User()->id,
                ];
                $mp = Products::where('sku', $row['sku'])->first();
                if ($mp == null) {
                    $row['payload'] = json_encode([
                        'name' => $product['title'],
                        'image_url' => $product['image']['src']
                    ]);
                    $row['type'] = 'delete';
                } else {
                    $cost = Products::where('sku', $row['sku'])->pluck('price')->first();
                    $row['payload'] = json_encode([
                        'cost' => $cost,
                        'profit' => ($product['variants'][0]['price'] - $cost) / $cost * 100,
                        'name' => $product['title'],
                        'image_url' => $product['image']['src']
                    ]);
                    $row['type'] = 'migration';
                }
                DB::table('temp_migrate_products')->insert($row);
            }
            if($parameters['index'] == count($products['body']['products']) -1){
                $mig_products = DB::table('temp_migrate_products')->where('user_id', Auth::User()->id);
                $total_count = DB::table('temp_migrate_products')->count();
                $mig_products = DB::table('temp_migrate_products')->skip(0)->take(10)->get();
                return json_encode([
                    'index' => $parameters['index'],
                    'mig_products' => [
                        'products' => $mig_products,
                    ],
                    'total_count' => $total_count,
                    'page_size' => 10,
                    'page_number' => 1
                ]);
            }
            else{
                return json_encode([
                    'index' => $parameters['index']
                ]);
            }
        }

        if ($parameters['action'] == 'migrate-products') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $mig_products = DB::table('temp_migrate_products')->where('user_id', Auth::User()->id);
            $total_count = $mig_products->count();
            $mig_products = $mig_products->skip(($page_number -1) * $page_size)->take($page_size)->get();
            return json_encode([
                'mig_products' => [
                    'products' => $mig_products
                ],
                'page_size' => $page_size,
                'page_number' => $page_number,
                'total_count' => $total_count
            ]);
        }

        if ($parameters['action'] == 'set-default-profit') {
            $mig_skus = DB::table('temp_migrate_products')->where('user_id', Auth::User()->id)->where('type', 'migration')->pluck('sku');
            foreach ($mig_skus as $sku) {
                $payload = DB::table('temp_migrate_products')->where('user_id', Auth::User()->id)->where('sku', $sku)->pluck('payload')->first();
                $payload = json_decode($payload);
                $payload->profit = Settings::where('id_merchant', Auth::user()->id)->first()->set8;
                DB::table('temp_migrate_products')->where('user_id', Auth::User()->id)->where('sku', $sku)->update(['payload' => json_encode($payload), 'price' => $payload->cost * (100 + $payload->profit) / 100]);
            }
            return json_encode([
                'result' => true
            ]);
        }
    }

    public function import(Request $request)
    {
        $rows = [];
        foreach ($request->product_ids as $product_id) {
            $product = ImportList::where('id_customer', Auth::User()->id)->where('id_product', $product_id)->first();
            if ($product == null) {
                $rows[] = [
                    'id_customer' => Auth::User()->id,
                    'id_product' => $product_id
                ];
            }
        }
        DB::table('import_list')->insert($rows);
        return json_encode($request->product_ids);
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
