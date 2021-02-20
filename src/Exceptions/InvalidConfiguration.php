<?php

namespace NotificationChannels\HubspotEngagement\Exceptions;

class InvalidConfiguration extends \Exception
{
    public static function configurationNotSet()
    {
        return new static('In order to send notification via Hubspot Engagement you need to add credentials in the `hubspot` key of `config.services`.');
    }
}
