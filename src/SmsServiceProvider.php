<?php

namespace Nuwira\Smsgw;

use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/sms.php' => config_path('sms.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/sms.php', 'sms'
        );

        $this->app->bind('nuwira-sms', function () {
            return new Sms();
        });
    }
}
