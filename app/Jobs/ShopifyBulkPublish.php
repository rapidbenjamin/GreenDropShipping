<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
            $collections = ShopifyAdminApi::getCollections($this->user);

            do {
                $response_product = ShopifyAdminApi::createProduct($this->user, $product,$this->published);
                if ((int)$response_product['result'] == 1) {

                    $row = ImportList::where('id',$product['id'])->get()->first();
                    $myProducts = new MyProducts();
                    $myProducts->id_customer = $this->user->id;
                    $myProducts->id_imp_product = $product['id'];
                    $myProducts->profit =  $product['profit'];
                    $myProducts->id_shopify = $response_product['shopify_id'];
                    $myProducts->id_variant_shopify = $response_product['variant_id'];
                    $myProducts->id_product = $row['id_product'];
                    $myProducts->inventory_item_id_shopify = $response_product['inventory_item_id'];
                    $myProducts->save();

                    $collections_split = explode(',', $product['collections']);

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
                    Log::info($product['name'] . ' - retry-after: ' . (int)$response_product['retry-after']);
                    sleep((int)$response_product['retry-after']);
                }
                $i++;
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
