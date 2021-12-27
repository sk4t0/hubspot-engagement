<?php

namespace NotificationChannels\HubspotEngagement;

use Illuminate\Support\ServiceProvider;

class HubspotEngagementServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningUnitTests()) {
            $this->loadViewsFrom(__DIR__ . '/../resources/views', 'hubspot-engagement');
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/hubspot.php', 'hubspot');

        $this->publishes(
            [
                __DIR__ . '/../config/hubspot.php' => config_path('hubspot.php'),
            ]
        );

    }

    /**
     * Register the application services.
     */
    public function register()
    {
        
    }
}
