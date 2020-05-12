<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class ExchangeRateServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('exchange_rate', function ($app) {
            return new \App\ExchangeRate\Converter($app);
        });
    }

    public function provides()
    {
        return ['exchange_rate'];
    }
}
