<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use App\Products;

use App\MyProducts;

use App\ProductsToSend;

use Illuminate\Support\Facades\Auth;

use App\Libraries\Shopify\ShopifyAdminApi;

use App\ImportList;

use App\Jobs\ShopifyBulkPublish;

use App\Settings;

use App\ShopifyBulk;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;



class ImportListController extends Controller

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

        $this->authorize('view-merchant-import-list');

        $prods = Products::select('products.*', 'import_list.id as id_import_list')

            ->join('import_list', 'import_list.id_product', '=', 'products.id')

            //->join('my_products', 'my_products.id_imp_product', '=', 'import_list.id')

            ->whereNotIn('import_list.id', MyProducts::where('id_customer', Auth::User()->id)->pluck('id_imp_product'))

            ->where('import_list.id_customer', Auth::user()->id)->orderBy('import_list.updated_at', 'desc')->paginate(50);



        foreach ($prods as $product) {

            if ($product['images'] != null && count(json_decode($product['images'])) > 0)

                $product['image_url'] = env('URL_MAGENTO_IMAGES') . json_decode($product['images'])[0]->file;



            $search = new SearchController;

            $product['description'] = $search->getAttributeByCode($product, 'description');

            $product['size'] = $search->getAttributeByCode($product, 'size');

            $product['images'] = json_decode($product['images']);

            $product['ship_height'] = round($search->getAttributeByCode($product, 'ship_height'), 2);

            $product['ship_width'] = round($search->getAttributeByCode($product, 'ship_width'), 2);

            $product['ship_length'] = round($search->getAttributeByCode($product, 'ship_length'), 2);

        }

        $settings = Settings::where('id_merchant', Auth::user()->id)->first();

        if ($settings == null) {

            $settings = new Settings();

            $settings->set8 = 0;

        }

        return view('import-list', array(

            'array_products' => $prods,

            'profit' => $settings->set8

        ));

    }



    public function publishShopify(Request $request)

    {

        $this->authorize('plan_publish-product-import-list');

        $settings = Settings::where('id_merchant', Auth::User()->id)->first();

        $published = false;

        if ($settings != null) {

            $published = $settings->set1 == 1;

        }

        ShopifyBulkPublish::dispatchNow(Auth::User(), $request->product, $published);

        $myproduct = MyProducts::select('id_shopify')->where('id_customer', Auth::User()->id)->where('id_imp_product', $request->product['id'])->first();

        return response()->json(array(

            'result' => $myproduct != null,

            'id_shopify' => $myproduct != null ? $myproduct->id_shopify : 0

        ));

    }



    public function publishAllShopify(Request $request)

    {

        $this->authorize('plan_bulk-publish-product-import-list');
/*
        //Save products in DB (this products will be published by cron)

        foreach($request->products as $pr){
            $sendToShopify = new ProductsToSend;

            $sendToShopify->id_product = $pr['id'];
            $sendToShopify->name = $pr['name'];
            $sendToShopify->weight = $pr['weight'];
            $sendToShopify->price = $pr['price'];
            $sendToShopify->description = $pr['description'];
            $sendToShopify->product_type = $pr['product_type'];
            $sendToShopify->tags = $pr['tags'];
            $sendToShopify->collections = $pr['collections'];
            $sendToShopify->sku = $pr['sku'];
            $sendToShopify->profit = $pr['profit'];
            $sendToShopify->images = '';
            $sendToShopify->id_merchant = Auth::User()->id;

            $sendToShopify->save();
        }



        Log::info("LOG-REQUESTBULKPRODUCTS-new");
        Log::info($request->products);


        $result = 'ok';

*/        

      //Developed by Oscar

        $result = 'error';

        $settings = Settings::where('id_merchant', Auth::User()->id)->first();

        $published = false;

        if ($settings != null) {

            $published = $settings->set1 == 1;

        }

        foreach ($request->products as $product) {
            //if(ShopifyBulkPublish::dispatch(Auth::User(), $product,$published)->delay(now()->addSeconds(10)))
            if(ShopifyBulkPublish::dispatchNow(Auth::User(), $product,$published))
                $result = 'ok';
            sleep(10);

        }

        
        return response()->json(array('result' => $result));
    }

}
