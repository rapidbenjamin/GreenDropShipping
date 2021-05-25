<?php

namespace App\Http\Controllers;

use App\Libraries\Shopify\ShopifyAdminApi;
use App\MyProducts;
use Illuminate\Http\Request;
use App\Products;
use Illuminate\Support\Facades\Auth;


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
        $prods = Products::select('products.*', 'my_products.id_imp_product as id_my_product','my_products.id_shopify','my_products.id as id_my_products','my_products.profit')
            ->join('import_list', 'import_list.id_product', '=', 'products.id')
            ->join('my_products', 'my_products.id_imp_product', '=', 'import_list.id')
            ->where('my_products.id_customer', Auth::user()->id)->whereNull('my_products.deleted_at')->orderBy('my_products.id', 'desc')->paginate(10);

        $search = new SearchController;
        foreach ($prods as $product) {
            $product['brand'] = $search->getAttributeByCode($product, 'brand');
            if ($product->images != null && count(json_decode($product->images)) > 0)
                $product->image_url = env('URL_MAGENTO_IMAGES') . json_decode($product->images)[0]->file;
        }
        return view('my-products', ['prods' => $prods]);
    }

    public function deleteProduct(MyProducts $product){
        $result = ShopifyAdminApi::deleteProduct(Auth::User(),$product->id_shopify);
        if(isset($result['HTTP_CODE'])&&$result['HTTP_CODE']==200){
            $product->delete();
        }else{
            return response()->json(['message' => 'fail to delete product in shopify'], $result['HTTP_CODE']);
        }
    }
}
