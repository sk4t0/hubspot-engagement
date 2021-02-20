<?php

namespace NotificationChannels\HubspotEngagement;

use NotificationChannels\HubspotEngagement\Exceptions\CouldNotSendNotification;
use Illuminate\Notifications\Notification;
use SevenShores\Hubspot\Factory as Hubspot;

class HubspotEngagementChannel
{
    /**
     * @var Hubspot
     */
    protected $hubspot;

    public function __construct(Hubspot $hubspot)
    {
        $this->hubspot = $hubspot;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws \NotificationChannels\HubspotEngagement\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification): ?array
    {
        $message = $notification->toMail($notifiable);

        $this->hubspot->engagements()->create(
            [
                "active" => true,
                "ownerId" => $notifiable->hubspot_owner_id ?? null,
                "type" => "EMAIL",
                "timestamp" => now()->timestamp
            ],
            [
                "contactIds"=> [$notifiable->hubspot_contact_id ?? null],
            ],
            [],
            [
                "from"=> [
                    "email" => $message->from[0],
                    "firstName" => $message->from[1]
                ],
                "to" => [[
                    "email" => $notifiable->routeNotificationForMail($notification)
                ]],
                "cc" => [ !empty($message->cc) ? [ "email" =>   $message->cc[0][0] ]: null],
                "subject" =>  $message->subject,
                "html" =>  $message->render(),
            ]
        );
        //$response = [a call to the api of your notification send]

//        if ($response->error) { // replace this by the code need to check for errors
//            throw CouldNotSendNotification::serviceRespondedWithAnError($response);
//        }
    }
}
