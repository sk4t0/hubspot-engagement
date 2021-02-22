<?php

namespace NotificationChannels\HubspotEngagement\Test;

use Illuminate\Notifications\Notification;

class Notifiable
{
    use \Illuminate\Notifications\Notifiable;

    /**
     * @return int
     */
    public function routeNotificationForMail(Notification $notification)
    {
        return 'email@email.it';
    }

    public function getHubspotOwnerIdAttribute($value){
        return 123456789 ;
    }
    public function getHubspotContactIdAttribute($value){
        return 987654321 ;
    }
}
