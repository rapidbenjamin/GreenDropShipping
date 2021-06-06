<?php

namespace App\Console;


use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Libraries\SyncLib;

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



          //SHOPIFY UPGRATED
        $schedule->call(function(){

            SyncLib::shopifyUpgraded();

        })->everyMinute();

        //STOCK
        $schedule->call(function(){
            //UPDATE STOCK FROM MAGENTO TO MIDDLEWARE (this is a View with quantity and saleable setting for each sku)
            SyncLib::syncStock();

        })->everyFiveMinutes();

        //ShopifySTOCK
        $schedule->call(function(){
            //UPDATE STOCK IN SHOPIFY STORES
            SyncLib::syncShopifyStock('cron');

        })->everyFiveMinutes();

        //Tracking Number
        $schedule->call(function(){

            SyncLib::setTrackingNumber();

        })->everyFiveMinutes();


         //Update WP membershiop tokens
        $schedule->call(function(){

            SyncLib::syncWP();

        })->everyFiveMinutes();



        //Update Order Status
        $schedule->call(function(){

            SyncLib::updateStatusWhenCancelingMagento();

        })->everyFiveMinutes();


        //Magento Categories
        $schedule->call(function(){

            SyncLib::syncCategories();

        })->everyFiveMinutes();


        //Magento Products
        $schedule->call(function(){

            SyncLib::syncProducts();

        })->everyFiveMinutes();

    }//Close schedule



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
