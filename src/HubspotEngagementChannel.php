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
        $metadataArray = [
            'from' => [
                'email' => $message->from ? $message->from[0] : config('mail.from.address'),
            ],
            'to' => [[
                'email' => $notifiable->routeNotificationForMail($notification),
            ]],
            'cc' => [],
            'bcc' => [],
            'subject' => $message->subject,
            'html' => $message->render(),
        ];

        $fromName = $message->from ? $message->from[1] : config('mail.from.name');
        if ($fromName) {
            $metadataArray['from']['firstName'] = $fromName;
        }

        if (! empty($message->cc)) {
            foreach ($message->cc as $cc) {
                $emailArray = ['email' => $cc[0]];
                if (! empty($cc[1])) {
                    $emailArray['firstName'] = $cc[1];
                }
                $metadataArray['cc'][] = $emailArray;
            }
        }
        if (! empty($message->bcc)) {
            foreach ($message->bcc as $bcc) {
                $emailArray = ['email' => $bcc[0]];
                if (! empty($bcc[1])) {
                    $emailArray['firstName'] = $bcc[1];
                }
                $metadataArray['bcc'][] = $emailArray;
            }
        }

        try {
            $e = $this->hubspot->engagements()->create($engagementArray, $associationsArray, $metadataArray);
        } catch (BadRequest $e) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($e->getMessage());
        }

        return $e;
    }
}
