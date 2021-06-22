<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Libraries\Shopify\ShopifyAdminApi;
use App\MyProducts;
use App\ImportList;
use Illuminate\Support\Facades\Log;
use Throwable;

class ShopifyBulkPublish implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter = 10;
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    protected $products;
    protected $user;
    protected $published;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $products,$published)
    {
        //
        $this->products = $products;
        $this->user = $user;
        $this->published = $published;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->products as $product) {
            $attemps = 3;
            $i = 0;
            if (json_decode($product)->tags != null) {
                foreach (explode(',', json_decode($product)->tags) as $str) {
                    if (trim($str) != '') {
                        $tag = DB::table('user_collections_tags_types')->where([['user_id', Auth::User()->id], ['type', 'T'], ['value', trim($str)]])->first();
                        if ($tag == null) {
                            $tag = [
                                'user_id' => Auth::User()->id,
                                'type' => 'T',
                                'value' => trim($str)
                            ];
                            DB::table('user_collections_tags_types')->insert($tag);
                        }
                    }
                }
            }
            if (json_decode($product)->collections != null) {
                $collection = DB::table('user_collections_tags_types')->where([['user_id', Auth::User()->id], ['type', 'C'], ['value', json_decode($product)->collections]])->first();
                if ($collection == null) {
                    $collection = [
                        'user_id' => Auth::User()->id,
                        'type' => 'C',
                        'value' => json_decode($product)->collections
                    ];
                    DB::table('user_collections_tags_types')->insert($collection);
                }
            }
            if (json_decode($product)->product_type != null) {
                $product_type = DB::table('user_collections_tags_types')->where([['user_id', Auth::User()->id], ['type', 'X'], ['value', json_decode($product)->product_type]])->first();
                if ($product_type == null) {
                    $product_type = [
                        'user_id' => Auth::User()->id,
                        'type' => 'X',
                        'value' => json_decode($product)->product_type
                    ];
                    DB::table('user_collections_tags_types')->insert($product_type);
                }
            }
            $collections = ShopifyAdminApi::getCollections($this->user);

            do {
                $prod = MyProducts::where('id_customer', Auth::User()->id)->where('id_imp_product', json_decode($product)->id)->first();
                if ($prod == null) {
                    $response_product = ShopifyAdminApi::createProduct($this->user, json_decode($product),$this->published);
                    if ((int)$response_product['result'] == 1) {

                        $row = ImportList::where('id',json_decode($product)->id)->get()->first();
                        $myProducts = new MyProducts();
                        $myProducts->id_customer = $this->user->id;
                        $myProducts->id_imp_product = json_decode($product)->id;
                        $myProducts->profit =  json_decode($product)->profit;
                        $myProducts->id_shopify = $response_product['shopify_id'];
                        $myProducts->id_variant_shopify = $response_product['variant_id'];
                        $myProducts->id_product = $row['id_product'];
                        $myProducts->inventory_item_id_shopify = $response_product['inventory_item_id'];
                        $myProducts->stock = $response_product['inventory_quantity'];
                        $myProducts->save();

                        $collections_split = explode(',', json_decode($product)->collections);

                        foreach ($collections_split as $item) {
                            $filtered = $collections->first(function ($value) use ($item) {
                                return trim($item) != '' && $value['name'] == trim($item);
                            });

                            if ($filtered == null) { //no existe
                                $collectionId = ShopifyAdminApi::createCustomCollection($this->user, $item);
                            }
                            else{
                                $collectionId = $filtered['id'];
                            }
                            if ($collectionId != 0) {
                                $result = ShopifyAdminApi::addProductToCustomCollection($this->user, $myProducts->id_shopify, $collectionId);
                            }
                        }
                        break;
                    } else if ((int)$response_product['result'] == 2) {
                        Log::info(json_decode($product)->name . ' - retry-after: ' . (int)$response_product['retry-after']);
                        sleep((int)$response_product['retry-after']);
                    }
                    $i++;
                } else break;
            } while ($attemps == $i);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error($exception);
    }
}
