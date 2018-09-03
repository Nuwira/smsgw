<?php

namespace Nuwira\Smsgw;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
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
            $baseUrl = Config::get('sms.base_url');
            $apiKey = Config::get('sms.api_key');
            $locale = Config::get('app.locale');

            $guzzle = new Client([
                'base_uri' => $baseUrl,
                'timeout' => 60,
                'http_errors' => true,
                'headers' => [
                    'Authorization' => $apiKey,
                ],
            ]);

            $sms = new Sms($guzzle, app()->getLocale());

            return $sms;
        });
    }
}
