<?php


namespace NotificationChannels\HubspotEngagement\Test;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestLineMailNotification extends Notification
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Subject')
            ->greeting('Greeting')
            ->line('Line')
            ->action('button','https://www.google.it');
    }
}
