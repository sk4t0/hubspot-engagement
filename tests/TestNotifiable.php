<?php

namespace NotificationChannels\HubspotEngagement\Test;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class TestNotifiable extends Model
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
