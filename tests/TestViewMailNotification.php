<?php


namespace NotificationChannels\HubspotEngagement\Test;


use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\HubspotEngagement\HubspotEngagementChannel;

class TestViewMailNotification extends Notification
{
    public function via($notifiable)
    {
        return [HubspotEngagementChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Subject')
            ->from('from3@email.com','From3')
            ->view('email_test_view',[])
            ->cc('cc@email.com','cc_name')
            ->bcc('bcc@email.com','bcc_name');
    }
}
