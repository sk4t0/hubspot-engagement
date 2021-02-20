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
        $this->app->when(HubspotEngagementChannel::class)
            ->needs(Hubspot::class)
            ->give(function () {
                $hubspotConfig = config('services.hubspot');

                if (is_null($hubspotConfig)) {
                    throw InvalidConfiguration::configurationNotSet();
                }

                return SevenShores\Hubspot\Factory::create(
                    $hubspotConfig['api_key'],
                    null,
                    $hubspotConfig['client_options'] ?? []
                );
            });

    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
