<?php

namespace NotificationChannels\HubspotEngagement;

use NotificationChannels\HubspotEngagement\Exceptions\CouldNotSendNotification;
use Illuminate\Notifications\Notification;
use SevenShores\Hubspot\Factory as Hubspot;
use SevenShores\Hubspot\Exceptions\BadRequest;

class HubspotEngagementChannel
{
    /**
     * @var Hubspot
     */
    protected $hubspot;

    protected $mail_config;

    /**
     * HubspotEngagementChannel constructor.
     * @param Hubspot $hubspot
     * @param array|null $mail_config
     */
    public function __construct(Hubspot $hubspot, Array $mail_config = null)
    {
        $this->hubspot = $hubspot;
        if($mail_config){
            $this->mail_config = $mail_config;
        }else{
            $this->mail_config = config('mail.from');
        }
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
            "from" => [
                "email" => $message->from ? $message->from[0] : $this->mail_config['address'],
                "firstName" => $message->from ? $message->from[1] : $this->mail_config['name']
            ],
            "to" => [[
                "email" => $notifiable->routeNotificationForMail($notification)
            ]],
            "cc" => [],
            "bcc" => [],
            "subject" => $message->subject,
            "html" => $message->render()
        ];
        if (!empty($message->cc)) {
            foreach ($message->cc as $cc) {
                $emailMetaData["cc"][] = ["email" => $cc[0]];
            }
        }
        if (!empty($message->bcc)) {
            foreach ($message->bcc as $bcc) {
                $emailMetaData["bcc"][] = ["email" => $bcc[0]];
            }
        }

        try {
            $e = $this->hubspot->engagements()->create(
                [
                    "active" => true,
                    "ownerId" => $notifiable->hubspot_owner_id ?? null,
                    "type" => "EMAIL",
                    "timestamp" => now()->getPreciseTimestamp(3)
                ],
                [
                    "contactIds" => $notifiable->hubspot_contact_id ? [$notifiable->hubspot_contact_id] : [],
                    "companyIds" => [],
                    "dealIds" => [],
                    "ownerIds" => [],
                    "ticketIds" => []
                ],
                $emailMetaData,
                []
            );
        } catch (BadRequest $e) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($e->getMessage());
            return null;
        }
        return $e;
    }
}
