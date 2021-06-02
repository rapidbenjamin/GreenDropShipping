<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Libraries\Shopify\ShopifyAdminApi;
use Illuminate\Support\Facades\Log;
use Throwable;

class ShopifyBulkDelete implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter = 1;
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    protected $product_ids;
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $product_ids)
    {
        //
        $this->product_ids = $product_ids;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->product_ids as $product_id) {
            $attemps = 3;
            $i = 0;
            do {
                $response_product = ShopifyAdminApi::deleteProduct($this->user, $product_id);
                if ($response_product['HTTP_CODE'] == 200) {
                    \DB::table('my_products')->where('id_shopify', $product_id)->delete();
                    \DB::table('temp_publish_products')->where('payload', $product_id)->delete();
                    break;
                } else {
                    Log::info($product_id . ' - retry-after: ' . (int)$response_product['retry-after']);
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
