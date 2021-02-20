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
            "from" => [
                "email" => $message->from ? $message->from[0] : config('mail.from.address'),
                "firstName" => $message->from ? $message->from[1] : config('mail.from.name')
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
        dd($e);
        return $e;
    }
}
