<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Products;
use Illuminate\Support\Facades\Auth;


class MyProductsController extends Controller
{

    public function index()
    {
        $prods = Products::select('products.*', 'my_products.id_imp_product as id_my_product')
            ->join('my_products', 'my_products.id_imp_product', '=', 'products.id')
            ->where('id_customer', Auth::user()->id)->orderBy('my_products.updated_at', 'desc')->paginate(10);

        $search = new SearchController;
        foreach ($prods as $product) {
            $product['brand'] = $search->getAttributeByCode($product, 'brand');
            if ($product->images != null && count(json_decode($product->images)) > 0)
                $product->image_url = env('URL_MAGENTO_IMAGES') . json_decode($product->images)[0]->file;
        }

        return view('my-products', ['prods' => $prods]);
    }
}
