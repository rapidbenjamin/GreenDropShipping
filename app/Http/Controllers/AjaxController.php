<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DashboardSteps;
use App\ImportList;
use App\MyProducts;
use App\Settings;
use App\Order;
use App\User;
use App\Products;

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

            $row = new ImportList;
            $row->id_customer = Auth::user()->id;
            $row->id_product = $parameters['id_product'];
            $row->save();

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
                    $product->image_url = env('URL_MAGENTO_IMAGES') . json_decode($product->images)[0]->file;
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
                    $product['image_url'] = env('URL_MAGENTO_IMAGES') . json_decode($product['images'])[0]->file;

                $search = new SearchController;
                $images = [];
                foreach (json_decode($product['images']) as $image) {
                    $images[] = env('URL_MAGENTO_IMAGES') . $image->file;
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
