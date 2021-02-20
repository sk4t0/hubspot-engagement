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
        $emailMetaData = [
            "from"=> [
                "email" => $message->from[0],
                "firstName" => $message->from[1]
            ],
            "to" => [[
                "email" => $notifiable->routeNotificationForMail($notification)
            ]],
            "cc" => [],
            "bcc" => [],
            "subject" =>  $message->subject,
            "html" =>  $message->render()
        ];
        if(!empty($message->cc)){
            foreach($message->cc as $cc){
                $emailMetaData["cc"][] = ["email" => $cc[0]];
            }
        }
        if(!empty($message->bcc)){
            foreach($message->bcc as $bcc){
                $emailMetaData["bcc"][] = ["email" => $bcc[0]];
            }
        }
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
            $emailMetaData
        );
        //$response = [a call to the api of your notification send]

//        if ($response->error) { // replace this by the code need to check for errors
//            throw CouldNotSendNotification::serviceRespondedWithAnError($response);
//        }
    }
}
