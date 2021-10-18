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
        $total_count = Products::select('products.*', 'my_products.id_imp_product as id_my_product', 'my_products.id_shopify', 'my_products.id as id_my_products', 'my_products.profit')
            ->join('import_list', 'import_list.id_product', '=', 'products.id')
            ->join('my_products', 'my_products.id_imp_product', '=', 'import_list.id')
            ->where('my_products.id_customer', Auth::user()->id)
            ->whereNull('my_products.deleted_at')->count();
        
        return view('my-products_v2', [
            'total_count' => $total_count
        ]);
    }

    public function deleteProduct(Request $request)
    {
        $this->authorize('view-merchant-my-products');
        $this->authorize('plan_view-my-products');
        $before = MyProducts::select('id_shopify')
            ->where('id_customer', Auth::User()->id)
            ->where('id_shopify', $request->id_shopify)
            ->first();
        ShopifyBulkDelete::dispatchNow(Auth::User(), [$request->id_shopify], 'MyProducts');
        $after = MyProducts::select('id_shopify')
            ->where('id_customer', Auth::User()->id)
            ->where('id_shopify', $request->id_shopify)
            ->first();
        
        return response()->json([
            'result' => $before != null && $after == null
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
        ShopifyBulkDelete::dispatch(Auth::User(), $temp_product_ids, 'MyProducts');
    }

    public function checkDeleteProducts(Request $request)
    {
        $this->authorize('view-merchant-my-products');
        $this->authorize('plan_view-my-products');

        $my_shopify_ids = DB::table('my_products')
            ->where('id_customer', Auth::User()->id)
            ->whereIn('id_shopify', $request->product_shopify_ids)
            ->pluck('id_shopify');
        $data = [];
        foreach ($request->product_shopify_ids as $product_shopify_id) {
            $flag = true;
            foreach ($my_shopify_ids as $my_shopify_id) {
                if ($product_shopify_id == $my_shopify_id) {
                    $flag = false;
                }
            }
            if ($flag) {
                $data[] = $product_shopify_id;
            }
        }

        return response()->json([
            'product_shopify_ids' => $data
        ]);
    }
}
