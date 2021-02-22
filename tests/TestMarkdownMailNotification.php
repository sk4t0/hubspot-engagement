<?php


namespace NotificationChannels\HubspotEngagement\Test;


use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\HubspotEngagement\HubspotEngagementChannel;

class TestMarkdownMailNotification extends Notification
{
    public function via($notifiable)
    {
        return [HubspotEngagementChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Subject')
            ->from('from2@email.com')
            ->cc('cc@email.com','cc_name')
            ->cc('cc2@email.com')
            ->bcc('bcc@email.com')
            ->bcc('bcc2@email.com','bcc2_name')
            ->markdown('email_test_markdown',[]);
    }
}
