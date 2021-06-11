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
		$imported_ids = ImportList::where('id_customer', Auth::User()->id)->pluck('id_product');
		return view('search', [
			'imported_ids' => $imported_ids
		]);
	}

	public function show(Products $products)
	{
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
