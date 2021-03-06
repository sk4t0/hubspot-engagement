<?php

namespace NotificationChannels\HubspotEngagement;

use Illuminate\Support\Facades\Http;
use Illuminate\Notifications\Notification;
use NotificationChannels\HubspotEngagement\Exceptions\CouldNotSendNotification;

class HubspotEngagementChannel
{
    /**
     * HubspotEngagementChannel constructor.
     */
    public function __construct()
    {
    
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

        $message = $notification->toMail($notifiable);

        $response = Http::post('https://api.hubapi.com/crm/v3/objects/emails?hapikey=' . config('hubspot.api_key'), [
            "properties" => [
                "hs_timestamp" => now()->getPreciseTimestamp(3),
                "hubspot_owner_id" => "38776675",
                "hs_email_direction" => "EMAIL",
                "hs_email_status" => "SENT",
                "hs_email_subject" => $message->subject,
                "hs_email_text" => (string) $message->render()]
            ]
        );
        $hubspotEmail = json_decode($response->body(), true);
        if($response->status() == 201) {
            $newResp = Http::put('https://api.hubapi.com/crm/v3/objects/emails/'.$hubspotEmail['id'].'/associations/contacts/'.$hubspotContactId.'/198?hapikey=' . config('hubspot.api_key'));
            if(!$newResp->status() == 200) {
                throw CouldNotSendNotification::serviceRespondedWithAnError($newResp['message']);
            }
        }else{
            throw CouldNotSendNotification::serviceRespondedWithAnError($response['message']);
        }

        return $hubspotEmail;
    }

}
