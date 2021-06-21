<?php

namespace NotificationChannels\HubspotEngagement;

use Illuminate\Support\ServiceProvider;
use NotificationChannels\HubspotEngagement\Exceptions\InvalidConfiguration;
use SevenShores\Hubspot\Factory as Hubspot;

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
        $this->app->when(HubspotEngagementChannel::class)
            ->needs(Hubspot::class)
            ->give(
                static function () {
                    $hubspotConfig = config('hubspot');

                    if (is_null($hubspotConfig)) {
                        throw InvalidConfiguration::configurationNotSet();
                    }

                    return Hubspot::create($hubspotConfig['api_key'], null, $hubspotConfig['client_options'] ?? []);
                }
            );
    }
}
