<?php

namespace App\Http\Controllers;

use App\Jobs\ShopifyBulkDelete;
use App\MyProducts;
use Illuminate\Http\Request;
use App\Products;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class MyProductsController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('view-merchant-my-products');
        $this->authorize('plan_view-my-products');
        $prods = Products::select('products.*', 'my_products.id_imp_product as id_my_product', 'my_products.id_shopify', 'my_products.id as id_my_products', 'my_products.profit')
            ->join('import_list', 'import_list.id_product', '=', 'products.id')
            ->join('my_products', 'my_products.id_imp_product', '=', 'import_list.id')
            ->where('my_products.id_customer', Auth::user()->id)->whereNull('my_products.deleted_at')->orderBy('my_products.id', 'desc');
        $total_count = $prods->count();
        $prods = $prods->paginate(10);
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
        return view('my-products_v2', ['prods' => $prods, 'total_count' => $total_count]);
    }

    public function deleteProduct(Request $request)
    {
        $this->authorize('view-merchant-my-products');
        $this->authorize('plan_view-my-products');
        ShopifyBulkDelete::dispatchNow(Auth::User(), [$request->product_id], 'MyProducts');
        return response()->json([
            'result' => true,
            'product_id' => $request->product_id
        ]);
    }

    public function deleteAllProduct(Request $request)
    {
        $this->authorize('view-merchant-my-products');
        $this->authorize('plan_view-my-products');
        $rows = [];
        foreach ($request->products as $product) {
            $rows[] = [
                'id' => $product['product_id'],
                'user_id' => Auth::user()->id,
                'payload' => $product['product_shopify_id'],
                'action' => 'delete'
            ];
        }
        DB::table('temp_publish_products')->insert($rows);
        $temp_product_ids = DB::table('temp_publish_products')
            ->where('user_id', Auth::user()->id)
            ->where('action', 'delete')
            ->pluck('payload');
        if (ShopifyBulkDelete::dispatch(Auth::User(), $temp_product_ids, 'MyProducts')) {
            $result = 'ok';
        }

        return response()->json(['result' => $result]);
    }

    public function checkDeleteProducts(Request $request)
    {
        $this->authorize('view-merchant-my-products');
        $this->authorize('plan_view-my-products');

        $product_shopify_ids = $request->product_shopify_ids;
        $result = DB::table('temp_publish_products')->where('user_id', Auth::User()->id)
            ->whereIn('payload', $product_shopify_ids)->where('action', 'delete')->pluck('payload');
        $data = [];
        foreach ($product_shopify_ids as $product_shopify_id) {
            $flag = true;
            foreach ($result as $res) {
                if ($product_shopify_id == $res) $flag = false;
            }
            if ($flag) $data[] = $product_shopify_id;
        }

        return response()->json([
            'product_shopify_ids' => $data
        ]);
    }
}
