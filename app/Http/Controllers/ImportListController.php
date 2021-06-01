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

        ShopifyBulkPublish::dispatchNow(Auth::User(), [json_encode((object) $request->product)], $published);

        $myproduct = MyProducts::select('id_shopify')->where('id_customer', Auth::User()->id)->where('id_imp_product', $request->product['id'])->first();

        return response()->json(array(

            'result' => $myproduct != null,

            'id_shopify' => $myproduct != null ? $myproduct->id_shopify : 0

        ));

    }



    public function publishAllShopify(Request $request)

    {

        $this->authorize('plan_bulk-publish-product-import-list');

        $result = 'error';

        $settings = Settings::where('id_merchant', Auth::User()->id)->first();

        $published = false;

        if ($settings != null) {

            $published = $settings->set1 == 1;

        }
        DB::statement(
            "CREATE TABLE IF NOT EXISTS `temp_publish_products` (
                `id` int(11) NOT NULL,
                `sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `payload` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $rows = [];

        foreach ($request->products as $product) {
            $row['id'] = $product['id'];
            $row['sku'] = $product['sku'];
            $row['payload'] = json_encode($product);
            $row['user_id'] = Auth::User()->id;
            $rows[] = $row;
        }
        DB::table('temp_publish_products')->insert($rows);
        $temp_publish_products = DB::table('temp_publish_products')->where('user_id', Auth::User()->id)->pluck('payload');
        if(ShopifyBulkPublish::dispatch(Auth::User(), json_decode($temp_publish_products) ,$published))
            $result = 'ok';

        return response()->json(array('result' => $result));
    }

    public function checkPublishProducts(Request $request)

    {

        $this->authorize('view-merchant-import-list');
        $user_id = $request->user_id;
        $product_ids = $request->product_ids;
        $prods = MyProducts::where('id_customer', $user_id)->whereIn('id_imp_product', $product_ids)->pluck('id_imp_product');
        DB::table('temp_publish_products')->whereIn('id', $prods)->delete();
        return response()->json(array(
            'result' => $prods != null,
            'id_shopify' => $prods != null ? $prods : 0
        ));

    }
}

