<?php

namespace NotificationChannels\HubspotEngagement;

use Illuminate\Notifications\Notification;
use NotificationChannels\HubspotEngagement\Exceptions\CouldNotSendNotification;
use SevenShores\Hubspot\Exceptions\BadRequest;
use SevenShores\Hubspot\Factory as Hubspot;

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
     */
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
        $hubspotContactId = $notifiable->getHubspotContactId();
        if (empty($hubspotContactId)) {
            return null;
        }

        $engagementArray = [
            'active' => true,
            'type' => 'EMAIL',
            'timestamp' => now()->getPreciseTimestamp(3),
        ];
        if ($notifiable->getHubspotOwnerId()) {
            $engagementArray['ownerId'] = $notifiable->getHubspotOwnerId();
        }

        $associationsArray = [
            'contactIds' => [$hubspotContactId],
            'companyIds' => [],
            'dealIds' => [],
            'ownerIds' => [],
            'ticketIds' => [],
        ];

        $message = $notification->toMail($notifiable);
        $metadataArray = $this->getMetadataFromMessage($message, $notifiable->routeNotificationForMail($notification));

        try {
            $e = (array) $this->hubspot->engagements()->create($engagementArray, $associationsArray, $metadataArray);
        } catch (BadRequest $e) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($e->getMessage());
        }

        return $e;
    }

    private function parseEmailAddresses($emailAdresses)
    {
        return collect($emailAdresses)->map(function ($address) {
            $emailArray = ['email' => $address[0]];
            if (! empty($address[1])) {
                $emailArray['firstName'] = $address[1];
            }

            return $emailArray;
        })->toArray();
    }

    private function getMetadataFromMessage($message, $to)
    {
        $metadataArray = [
            'from' => [
                'email' => $message->from ? $message->from[0] : config('mail.from.address'),
            ],
            'to' => [[
                'email' => $to,
            ]],
            'cc' => $this->parseEmailAddresses($message->cc),
            'bcc' => $this->parseEmailAddresses($message->bcc),
            'subject' => $message->subject,
            'html' => $message->render(),
        ];

        $fromName = $message->from ? $message->from[1] : config('mail.from.name');
        if ($fromName) {
            $metadataArray['from']['firstName'] = $fromName;
        }

        return $metadataArray;
    }
}
