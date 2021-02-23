<?php

namespace NotificationChannels\HubspotEngagement\Test;

use Illuminate\Notifications\Notification;

class TestNotifiableWithoutOwnerId
{
    use \Illuminate\Notifications\Notifiable;

    /**
     * @return int
     */
    public function routeNotificationForMail(Notification $notification)
    {
        return 'email@email.com';
    }

    public function getHubspotOwnerId(){
        return null ;
    }

    public function getHubspotContactId(){
        return 987654321 ;
    }
}
