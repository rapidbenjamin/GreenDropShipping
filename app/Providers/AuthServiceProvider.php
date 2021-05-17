<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $pemissions_roles = [

            /**Permissionns for admin & merchant */
            'view-admin-dashboard' => [
                'admin',
                'supervisor',
            ],
            'view-admin-orders' => [
                'admin',
                'supervisor',
            ],
            'view-admin-merchants' => [
                'admin',
                'supervisor',
            ],
            'view-admin-menu' => [
                'admin',
                'supervisor',
            ],
            'view-merchant-search' => [
                'merchant'
            ],
            'view-merchant-import-list' => [
                'merchant'
            ],
            'view-merchant-my-products' => [
                'merchant'
            ],
            'view-merchant-orders' => [
                'merchant'
            ],
            'view-merchant-orders-details' => [
                'merchant'
            ],
            'view-merchant-settings' => [
                'merchant'
            ],
            'view-merchant-plans' => [
                'merchant'
            ],
            'view-merchant-help' => [
                'merchant'
            ],
            'view-merchant-dashboard' => [
                'merchant'
            ],
            
        ];
        
        /**Permissionns for plan&basic&advanced */
        $pemissions_plans = [
            'plan_view-search-products' => [
                'free',
                'basic',
                'Advanced'
            ],
            'plan_view-search-products' => [
                'free',
                'basic',
                'Advanced'
            ],
            'plan_view-import-list' => [
                'free',
                'basic',
                'Advanced'
            ],
            'plan_delete-product-import-list' => [
                'basic',
                'Advanced'
            ],
            'plan_publish-product-import-list' => [
                'basic',
                'Advanced'
            ],
            'plan_bulk-publish-product-import-list' => [
                'basic',
                'Advanced'
            ],
            'plan_view-my-products' => [
                'basic',
                'Advanced'
            ],
            'plan_view-manage-orders' => [
                'basic',
                'Advanced'
            ],
        ];

        //apply permissions for roles
        foreach($pemissions_roles as $permission => $roles){
            Gate::define($permission, function ($user) use ($roles){
                return in_array($user->role, $roles);
            });
        }

        //apply permissions for roles
        foreach($pemissions_plans as $permission => $plans){
            Gate::define($permission, function ($user) use ($plans){
                return in_array($user->plan, $plans);
            });
        }
        


        //
    }
}
