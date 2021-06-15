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
		$imported_ids = ImportList::where('id_customer', Auth::User()->id)->pluck('id_product');
        $data = [];
        $level1 = Category::where('level', 2)->whereIn('position', [1,2,3,5,6,7])->where('is_active', 1)->pluck('id', 'name');
        $data[] = $level1;
        $level2 = Category::whereIn('parent_id', $level1)->where('is_active', 1)->pluck('id', 'name');
        $data[] = $level2;
        $level3 = Category::whereIn('parent_id', $level2)->where('is_active', 1)->pluck('id', 'name');
        $data[] = $level3;
        $level4 = Category::whereIn('parent_id', $level3)->where('is_active', 1)->pluck('id', 'name');
        $data[] = $level4;
        $level5 = Category::whereIn('parent_id', $level4)->where('is_active', 1)->pluck('id', 'name');
        $data[] = $level5;
        $level6 = Category::whereIn('parent_id', $level5)->where('is_active', 1)->pluck('id', 'name');
        $data[] = $level6;
        $level7 = Category::whereIn('parent_id', $level6)->where('is_active', 1)->pluck('id', 'name');
        $data[] = $level7;
        $level8 = Category::whereIn('parent_id', $level7)->where('is_active', 1)->pluck('id', 'name');
        $data[] = $level8;

        $names = [];
        foreach ($data as $values) {
            foreach ($values as $key => $value) {
                $names[] = $key;
            }
        }
		return view('search', [
			'imported_ids' => $imported_ids,
            'names' => json_encode($names)
		]);
	}

	public function show(Request $request, Products $products)
	{
        $action = 'search-products';
        if (isset($request->action)) {
            $action = 'my-products';
            $products->id_shopify = MyProducts::where('id_customer', Auth::User()->id)->where('id_product', $products->id)->pluck('id_shopify')[0];
        }
		$products->mini_images = [];

		if ($products->images != null && count(json_decode($products->images)) > 0) {
			$products->image_url = env('URL_MAGENTO_IMAGES'). '/e793809b0880f758cc547e70c93ae203' .json_decode($products->images)[0]->file;
			$images = [];

			foreach (json_decode($products->images) as $image) {
				$images[] = env('URL_MAGENTO_IMAGES'). '/dc09e1c71e492175f875827bcbf6a37c' .$image->file;
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
