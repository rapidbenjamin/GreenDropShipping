<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Products;
use App\Category;
use App\ImportList;
use App\MyProducts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
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

    public function index(Request $request)
    {
        $this->authorize('view-merchant-search');
        $imported_ids = Products::select('products.*', 'import_list.id as id_import_list')
            ->join('import_list', 'import_list.id_product', '=', 'products.id')
            ->whereNotIn('import_list.id', MyProducts::where('id_customer', Auth::User()->id)->pluck('id_imp_product'))
            ->where('import_list.id_customer', Auth::user()->id)->pluck('products.id');
        $myproduct_ids = MyProducts::where('id_customer', Auth::User()->id)->orderBy('id_product')->pluck('id_product');
        $shopify_ids = MyProducts::where('id_customer', Auth::User()->id)->orderBy('id_product')->pluck('id_shopify');
        return view('search', [
            'imported_ids' => $imported_ids,
            'myproduct_ids' => $myproduct_ids,
            'shopify_ids' => $shopify_ids,
        ]);
    }

    public function show(Products $products)
    {
        $action = 'search-products';
        $import_product = ImportList::where('id_customer', Auth::User()->id)->where('id_product', $products->id)->first();
        $my_product = MyProducts::where('id_customer', Auth::User()->id)->where('id_product', $products->id)->first();
        if ($import_product != null) {
            if ($my_product == null) {
                $action = 'added';
            } else {
                $action = 'my-product';
                $products->id_shopify = MyProducts::where('id_customer', Auth::User()->id)
                    ->where('id_product', $products->id)
                    ->pluck('id_shopify')[0];
            }
        }
        $products->mini_images = [];

        if ($products->images != null && count(json_decode($products->images)) > 0) {
            $products->image_url = env('URL_MAGENTO_IMAGES') . '/e793809b0880f758cc547e70c93ae203' . json_decode($products->images)[0]->file;
            $images = [];
            foreach (json_decode($products->images) as $image) {
                $images[] = env('URL_MAGENTO_IMAGES') . '/dc09e1c71e492175f875827bcbf6a37c' . $image->file;
            }
            $products->mini_images = $images;
        } else {
            $products->image_url = env('URL_MAGENTO_IMAGES') . '/e793809b0880f758cc547e70c93ae203no_selection';
            $images = [];
            foreach (json_decode($products->images) as $image) {
                $images[] = env('URL_MAGENTO_IMAGES') . '/dc09e1c71e492175f875827bcbf6a37cno_selection';
            }
            $products->mini_images = $images;
        }

        $products->brand = $this->getAttributeByCode($products, 'brand');
        $products->description = $this->getAttributeByCode($products, 'description');

        return view('search_detail', ['product' => $products, 'action' => $action]);
    }

    public function getCategories($parent_id = 2)
    {
        return Category::where('is_active', 1)->where('parent_id', $parent_id)->orderBy('position')->get();
    }

    public function getAttributeByCode($products, $code)
    {
        $found = collect(json_decode($products->attributes))->first(function ($item, $key) use ($code) {
            return $item->attribute_code == $code;
        });

        return $found != null ? $found->value : '';
    }
}
