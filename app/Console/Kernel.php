<?php

namespace App\Console;

use App\Libraries\Shopify\ShopifyAdminApi;
use App\MonthlyRecurringPlan;
use App\MyProducts;
use App\ShopifyBulk;
use App\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('queue:work --daemon')->everyMinute()->withoutOverlapping();
        $schedule->call(function () {
            $merchants = User::where('role', 'merchant')->whereNotNull('shopify_url')->where('plan', 'basic')->get();
            foreach ($merchants as $merchant) {
                $current = MonthlyRecurringPlan::where('current', 1)->where('merchant_id', $merchant->id)->first();
                if ($current != null) {
                    Log::info('checking recurring');
                    $start_date_next_month = date('Y-m-d', strtotime($current->end_date . ' + 1 day'));
                    $end_date_next_mont = date('Y-m-d', strtotime($start_date_next_month . ' + 1 month'));
                    
                    if ($end_date_next_mont == date('Y-m-d')) {
                        $new_current = new MonthlyRecurringPlan();
                        $new_current->merchant_id = $merchant->id;
                        $new_current->start_date = $start_date_next_month;
                        $new_current->end_date = $end_date_next_mont;
                        $new_current->current = true;
                        $new_current->save();

                        $current->current = false;
                        $current->save();
                    }
                }
            }

            //$recurring->
        })->everyMinute(); //Run the task every day at midnight

        /*
        $schedule->call(function () {
            
            Log::info('start shopify bulk1');
            //$shopifybulks = new ShopifyBulk();
            $attemps = 1;
            foreach (ShopifyBulk::where('in_process', 0)->get() as $bulk) {
                //$i = 0;
                $user = User::find($bulk->merchant_id);
                $product = json_decode($bulk->payload, true);
                $bulk->in_process = 1;
                $bulk->save();

                for ($i = 0; $i < $attemps; $i++) {
                    $response_product = ShopifyAdminApi::createProduct($user, $product);
                    if ((int)$response_product['result'] == 1) {
                        $myProducts = new MyProducts();
                        $myProducts->id_customer = $user->id;
                        $myProducts->id_imp_product = $product['id'];
                        $myProducts->profit =  $product['profit'];
                        $myProducts->id_shopify = $response_product['shopify_id'];
                        $myProducts->save();

                        foreach ($response_product['images'] as $image) {

                            for ($i = 0; $i < $attemps; $i++) {
                                $response_image = ShopifyAdminApi::publicImageProduct($user, $myProducts->id_shopify, $image);
                                if ((int)$response_image['result'] == 1) {
                                    $bulk->delete();
                                    break; //finish loop
                                } else if ((int)$response_image['result'] == 2) {
                                    Log::info('Shopify ID' . $myProducts->id_shopify . ' - retry-after: ' . (int)$response_image['retry-after']);
                                    sleep((int)$response_image['retry-after']);
                                }
                            }
                        }

                        break; //finish loop
                    } else if ((int)$response_product['result'] == 2) {
                        Log::info(' - retry-after: ' . (int)$response_product['retry-after']);
                        sleep((int)$response_product['retry-after']);
                    }
                }
            }
        })->everyMinute();*/
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
