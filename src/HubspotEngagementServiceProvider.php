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
        $this->app->when(HubspotEngagementChannel::class)
            ->needs(Hubspot::class)
            ->give(static function () {
                $hubspotConfig = config('services.hubspot');

                if (is_null($hubspotConfig)) {
                    throw InvalidConfiguration::configurationNotSet();
                }

                return Hubspot::create($hubspotConfig['api_key'], null,$hubspotConfig['client_options'] ?? []);
            });

    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
