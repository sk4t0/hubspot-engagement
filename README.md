# Hubspot Engagement Notifications Channel for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-notification-channels/hubspot-engagement.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/hubspot-engagement)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://scrutinizer-ci.com/g/trippo/hubspot-engagement/badges/build.png?b=master)](https://scrutinizer-ci.com/g/trippo/hubspot-engagement/build-status/master)
[![StyleCI](https://github.styleci.io/repos/339465312/shield?branch=master)](https://github.styleci.io/repos/339465312?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/:sensio_labs_id.svg?style=flat-square)](https://insight.sensiolabs.com/projects/:sensio_labs_id)
[![Quality Score](https://img.shields.io/scrutinizer/g/trippo/hubspot-engagement.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravel-notification-channels/hubspot-engagement)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/trippo/hubspot-engagement/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/trippo/hubspot-engagement/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/trippo/hubspot-engagement.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/hubspot-engagement)

This package makes it easy to log notifications
to [Hubspot Engagement](https://legacydocs.hubspot.com/docs/methods/engagements/engagements-overview) with Laravel 5.5+,
6.x, 7.x and 8.x

## Contents

- [Installation](#installation)
    - [Setting up the HubspotEngagement service](#setting-up-the-hubspotengagement-service)
- [Usage](#usage)
    - [Supported Engagement types](#supported-engagement-types)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Installation

You can install the package via composer:

```bash
composer require laravel-notification-channels/hubspot-engagement
```

### Setting up the HubspotEngagement service

Generate API Key from [Hubspot](https://knowledge.hubspot.com/integrations/how-do-i-get-my-hubspot-api-key).

Then, configure your Hubspot API Key and/or Guzzle options:

```php
// config/services.php
...
'hubspot' => [
    'api_key' => env('HUBSPOT_API_KEY'),
    'client_options' => [
        'http_errors' => true,
    ]
],
...
```

By setting http_errors to false, you will not receive any exceptions at all, but pure responses. For possible options,
see  [here](https://docs.guzzlephp.org/en/latest/request-options.html).

## Usage

You can now use the channel in your `via()` method inside the Notification class.

### Supported Engagement types

Currently is only supported **Email** type of Engagement. 

#### Email type
Your Notification class must have toMail method.
The package accepts: MailMessage lines notifications, MailMessage view notifications and Markdown mail notifications.

Data stored on Hubspot:
- Hubspot Owner Id => The Notifiable Model must have **getHubspotOwnerId()** function
- Hubspot Contact Id => The Notifiable Model must have **getHubspotContactId()** function
- Send at timestamp 
- from email
- from name
- subject
- html body
- to email
- cc emails
- bcc emails

### Example

#### Notification example
```php
use NotificationChannels\HubspotEngagement\HubspotEngagementChannel;
use Illuminate\Notifications\Notification;

class OrderConfirmation extends Notification
{
    ...
    public function via($notifiable)
    {
        return ['mail', HubspotEngagementChannel::class]];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject(__('order.order_confirm', ['code' => $this->order->code]));

        return $message->view(
            'emails.order', [
                'title' => __('order.order_confirm', ['code' => $this->order->code]),
                'order' => $this->order
            ]
        );
    }
    ...
}
```

#### Model example
```php
namespace App\Models;

class User extends Authenticatable{
    ...
     
    public function getHubspotOwnerId(){
        return $this->owner->hubspot_owner_id ?: ($this->owner_id ? $this->owner->hubspot_owner_id : null);
    }

    public function getHubspotContactId(){
        return $this->hubspot_contact_id;
    }
    ...
}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email info@albertoperipolli.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Alberto Peripolli](https://github.com/trippo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
