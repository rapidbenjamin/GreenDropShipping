<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Products;
use App\Category;
use App\ImportList;
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
		$dbProducts = (new Products)->newQuery();

        $only_in_stock = $request->only_in_stock;

		if ($request->select_category != "") {
			if ($request->txt_search == "") {
				$dbProducts = Products::where('categories', 'like', '%"category_id":"' . $request->select_category . '"%');
			} else {
				$subcategories = $this->getCategories($request->select_category);
				$dbProducts->where(function ($query) use ($subcategories, $request) {
					foreach ($subcategories as $sub) {
						$query->orWhere('categories', 'like', '%"category_id":"' . $sub->id . '"%');
					}

					$query = $query->orWhere('categories', 'like', '%"category_id":"' . $request->select_category . '"%');
				});

				$dbProducts = $dbProducts->where(function ($query) use ($request) {
					foreach (explode(" ", $request->txt_search) as $word) {
						$query->orwhere('name', 'like', '%' . $word . '%');
					}
				});
			}
		}

		if ($request->txt_search != "") {
			$dbProducts = $dbProducts->where(function ($query) use ($request) {
				foreach (explode(" ", $request->txt_search) as $word) {
					$query->orWhere('name', 'like', '%' . $word . '%');
				}
			});

            $dbProducts->orWhere('sku', trim($request->txt_search))
                ->orWhere('upc', trim($request->txt_search));
		}

        if ($only_in_stock) {
            $dbProducts->where('stock', '>', 0);
        }
		//Exclude products in import-list

		$notin = ImportList::where('id_customer', Auth::User()->id)->pluck('id_product');

		$dbProducts = $dbProducts->whereNotIn("id", $notin);

		if ($request->txt_search != "") {
			$dbProducts = $dbProducts->orderByRaw("CASE WHEN name LIKE '?%'  THEN 1 ELSE 0 END DESC", array($request->txt_search));
		}

		$dbProducts = $dbProducts->orderBy('stock','desc')->paginate(20);

		foreach ($dbProducts as $product) {
			if ($product->images != null && count(json_decode($product->images)) > 0) {
				$product->image_url = env('URL_MAGENTO_IMAGES') . json_decode($product->images)[0]->file;
				$product->brand = $this->getAttributeByCode($product, 'brand');
			}
		}

		return view('search', [
			'products' => $dbProducts, 'categories' => $this->getCategories(),
			'subcategories' => $this->getCategories($request->select_category)
		]);
	}

	public function show(Products $products)
	{
		$products->mini_images = [];

		if ($products->images != null && count(json_decode($products->images)) > 0) {
			$products->image_url = env('URL_MAGENTO_IMAGES') . json_decode($products->images)[0]->file;
			$images = [];

			foreach (json_decode($products->images) as $image) {
				$images[] = env('URL_MAGENTO_IMAGES') . $image->file;
			}

			$products->mini_images = $images;
		}

		$products->brand = $this->getAttributeByCode($products, 'brand');
		$products->description = $this->getAttributeByCode($products, 'description');

		return view('search_detail', ['product' => $products]);
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
