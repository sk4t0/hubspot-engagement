<?php

namespace NotificationChannels\HubspotEngagement\Test;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\HubspotEngagement\HubspotEngagementChannel;

class TestLineMailNotification extends Notification
{
    public function via($notifiable)
    {
        return [HubspotEngagementChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Subject')
            ->greeting('Greeting')
            ->line('Line')
            ->action('button', 'https://www.google.it');
    }
}
