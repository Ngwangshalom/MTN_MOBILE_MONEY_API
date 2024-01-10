<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class WGOrganisationMomoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('WGOrganisationMomoService', function ($app) {
            return new \App\Services\WGOrganisationMomoService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
