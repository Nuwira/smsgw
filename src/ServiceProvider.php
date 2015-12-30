<?php

namespace Nuwira\Smsgw;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('sms.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom( __DIR__.'/config/config.php', 'sms');
        
        $this->app->singleton('sms', function ($app) {
            return new Sms();
        });
        
    }
}
