<?php

namespace App\Http\Controllers;

use App\Products;
use App\Category;
use App\ImportList;
use App\MyProducts;
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

    public function index()
    {
        $this->authorize('view-merchant-search');
        $imported_ids = Products::select('products.*', 'import_list.id as id_import_list')
            ->join('import_list', 'import_list.id_product', '=', 'products.id')
            ->whereNotIn('import_list.id', MyProducts::where('id_customer', Auth::User()->id)->pluck('id_imp_product'))
            ->where('import_list.id_customer', Auth::user()->id)
            ->pluck('products.sku')->toArray();
        $myproduct_ids = MyProducts::where('id_customer', Auth::User()->id)
            ->join('products', 'id_product', '=', 'products.id')
            ->orderBy('id_product')
            ->pluck('products.sku')->toArray();
        $shopify_ids = MyProducts::where('id_customer', Auth::User()->id)
            ->orderBy('id_product')
            ->pluck('id_shopify');
        return view('search', [
            'imported_ids' => json_encode($imported_ids),
            'myproduct_ids' => json_encode($myproduct_ids),
            'shopify_ids' => $shopify_ids,
        ]);
    }

    public function show($sku)
    {
        $action = 'search-product';
        $product = Products::where('sku', $sku)->first();
        if ($product == null) {
            return view('search_detail', ['product' => $product, 'action' => $action]);
        }
        $import_product = ImportList::where('id_customer', Auth::User()->id)->where('id_product', $product->id)->first();
        $my_product = MyProducts::where('id_customer', Auth::User()->id)->where('id_product', $product->id)->first();
        if ($import_product != null) {
            if ($my_product == null) {
                $action = 'added';
            } else {
                $action = 'my-product';
                $product->id_shopify = MyProducts::where('id_customer', Auth::User()->id)
                    ->where('id_product', $product->id)
                    ->pluck('id_shopify')[0];
            }
        }
        $product->mini_images = [];
        $image_75 = 'dc09e1c71e492175f875827bcbf6a37c';
        $image_700 = '207e23213cf636ccdef205098cf3c8a3';
        $image_285 = 'e793809b0880f758cc547e70c93ae203';
        if ($product->images != null && count(json_decode($product->images)) > 0) {
            if (json_decode($product->images)[0]->file == '') {
                $product->image_url = [
                    'main' => '/img/default_image_285.png',
                    'gallery' => '/img/default_image_700.png'
                ];
                $images = [];
                foreach (json_decode($product->images) as $image) {
                    if ($image->file) {
                        $images[] = [
                            'main' => '/img/default_image_75.png',
                            'gallery' => '/img/default_image_700.png'
                        ];
                    }
                }
                $product->mini_images = $images;    
            } else {
                $product->image_url = [
                    'main' => env('URL_MAGENTO_IMAGES') . '/' . $image_285 . json_decode($product->images)[0]->file,
                    'gallery' => env('URL_MAGENTO_IMAGES') . '/' . $image_700 . json_decode($product->images)[0]->file,
                ];
                $images = [];
                foreach (json_decode($product->images) as $image) {
                    if ($image->file) {
                        $images[] = [
                            'main' => env('URL_MAGENTO_IMAGES') . '/' . $image_75 . $image->file,
                            'gallery' => env('URL_MAGENTO_IMAGES') . '/' . $image_700 . $image->file
                        ];
                    }
                }
                $product->mini_images = $images;
            }
        } else {
            $product->image_url = [
                'main' => '/img/default_image_285.png',
                'gallery' => '/img/default_image_700.png'
            ];
            $images = [];
            foreach (json_decode($product->images) as $image) {
                if ($image->file) {
                    $images[] = [
                        'main' => '/img/default_image_75.png',
                        'gallery' => '/img/default_image_700.png'
                    ];
                }
            }
            $product->mini_images = $images;
        }

        $product->brand = $this->getAttributeByCode($product, 'brand');
        $product->description = $this->getAttributeByCode($product, 'description');

        return view('search_detail', ['product' => $product, 'action' => $action]);
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
