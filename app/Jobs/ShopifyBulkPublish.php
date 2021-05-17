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

    protected $product;
    protected $user;
    protected $published;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $product,$published)
    {
        //
        $this->product = $product;
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
        $attemps = 3;
        $i = 0;
        $collections = ShopifyAdminApi::getCollections($this->user);

        do {
            $response_product = ShopifyAdminApi::createProduct($this->user, $this->product,$this->published);
            if ((int)$response_product['result'] == 1) {

                $row = ImportList::where('id',$this->product['id'])->get()->first();
                $myProducts = new MyProducts();
                $myProducts->id_customer = $this->user->id;
                $myProducts->id_imp_product = $this->product['id'];
                $myProducts->profit =  $this->product['profit'];
                $myProducts->id_shopify = $response_product['shopify_id'];
                $myProducts->id_variant_shopify = $response_product['variant_id'];
                $myProducts->id_product = $row['id_product'];
                $myProducts->inventory_item_id_shopify = $response_product['inventory_item_id'];
                $myProducts->save();


                //Update cost
                $response_create_cost = ShopifyAdminApi::updateCost($this->user, $myProducts->id_shopify, $myProducts->inventory_item_id_shopify, $this->product['cost']);

                foreach ($response_product['images'] as $image) {
                    $j = 0;
                    do {
                        $response_image = ShopifyAdminApi::publicImageProduct($this->user, $myProducts->id_shopify, $image);
                        if ((int)$response_image['result'] == 1) {
                            break;
                        } else if ((int)$response_image['result'] == 2) {
                            Log::info('Shopify ID' . $myProducts->id_shopify . ' - retry-after: ' . (int)$response_image['retry-after']);
                            sleep((int)$response_image['retry-after']);
                        }
                        $j++;
                    } while ($attemps == $j);
                }

                //$response_product = ShopifyAdminApi::createProduct($this->user, $this->product);
                //create collection
                //$name = 'collection1,collection2,collection3,collection4';

                $collections_split = explode(',', $this->product['collections']);

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
                Log::info($this->product['title'] . ' - retry-after: ' . (int)$response_product['retry-after']);
                sleep((int)$response_product['retry-after']);
            }
            $i++;
        } while ($attemps == $i);
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
