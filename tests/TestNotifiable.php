<?php

namespace NotificationChannels\HubspotEngagement\Test;

use Illuminate\Notifications\Notification;

class TestNotifiable
{
    use \Illuminate\Notifications\Notifiable;

    /**
     * @return int
     */
    public function routeNotificationForMail(Notification $notification)
    {
        return 'email@email.com';
    }

    public function getHubspotOwnerId()
    {
        return 123456789;
    }

    public function getHubspotContactId()
    {
        return 987654321;
    }
}
