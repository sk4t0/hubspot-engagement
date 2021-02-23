<?php

namespace NotificationChannels\HubspotEngagement\Test;

use Illuminate\Support\Facades\Config;
use Illuminate\View\FileViewFinder;
use Mockery;
use NotificationChannels\HubspotEngagement\Exceptions\CouldNotSendNotification;
use NotificationChannels\HubspotEngagement\Exceptions\InvalidConfiguration;
use NotificationChannels\HubspotEngagement\HubspotEngagementChannel;
use Orchestra\Testbench\TestCase;
use SevenShores\Hubspot\Exceptions\BadRequest;
use SevenShores\Hubspot\Factory as Hubspot;
use SevenShores\Hubspot\Http\Client;
use SevenShores\Hubspot\Resources\Engagements;

class ChannelFeatureTest extends TestCase
{
    /** @var Mockery\Mock */
    protected $hubspot;

    /** @var \NotificationChannels\HubspotEngagement\HubspotEngagementChannel */
    protected $channel;

    protected function getPackageProviders($app)
    {
        return ['NotificationChannels\HubspotEngagement\HubspotEngagementServiceProvider'];
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->hubspot = Mockery::mock(Hubspot::class);
        $this->channel = new HubspotEngagementChannel($this->hubspot);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function configSetUp()
    {
        $this->app['config']->set('mail.from.address', 'from@email.com');
        $this->app['config']->set('mail.from.name', 'from_name');
    }

    private function testViewSetting()
    {
        $this->app->bind('view.finder', function ($app) {
            $paths = [getcwd().'/'.('tests/resources/views')];

            return new FileViewFinder($app['files'], $paths);
        });
    }

    private function mockHubspot($client)
    {
        $this->hubspot->shouldReceive('engagements')->andReturn(new Engagements($client));
    }

    private function mockHubspotRequest()
    {
        $this->configSetUp();
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('request')->andReturnUsing(function ($type, $endpoint, $json) {
            return $json['json'];
        });
        $this->mockHubspot($client);
    }

    private function mockHubspotFailedResponse()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('request')->once()->andThrow(new BadRequest());
        $this->mockHubspot($client);
    }

    /** @test */
    public function it_throws_an_exception_when_it_is_not_configured()
    {
        Config::set('services.hubspot', null);
        $this->expectException(InvalidConfiguration::class);

        (new TestNotifiable())->notify(new TestLineMailNotification());
    }

    /** @test */
    public function it_throws_an_exception_when_it_could_not_send_the_notification()
    {
        $this->mockHubspotFailedResponse();
        $this->expectException(CouldNotSendNotification::class);

        $return = $this->channel->send(new TestNotifiable(), new TestLineMailNotification());
    }

    /** @test */
    public function it_not_send_a_notification_to_notifiable_without_contact_id()
    {
        $this->mockHubspotRequest();

        $channel_response = $this->channel->send(new TestNotifiableWithoutContactId(), new TestLineMailNotification());
        $this->assertNull($channel_response);
    }

    /** @test */
    public function it_send_a_notification_to_notifiable_with_contact_id()
    {
        $this->mockHubspotRequest();

        $channel_response = $this->channel->send(new TestNotifiable(), new TestLineMailNotification());
        $this->assertEquals($channel_response['engagement'], [
            'active' => true,
            'ownerId' => 123456789,
            'type' => 'EMAIL',
            'timestamp' => $channel_response['engagement']['timestamp'],
        ]);
        $this->assertEquals($channel_response['associations'], [
            'contactIds' => [987654321],
            'companyIds' => [],
            'dealIds' => [],
            'ownerIds' => [],
            'ticketIds' => [],
        ]);
    }

    /** @test */
    public function it_can_send_a_notification_to_notifiable_without_owner_id()
    {
        $this->mockHubspotRequest();

        $channel_response = $this->channel->send(new TestNotifiableWithoutOwnerId(), new TestLineMailNotification());
        $this->assertIsArray($channel_response);
        $this->assertEquals($channel_response['engagement'], [
            'active' => true,
            'type' => 'EMAIL',
            'timestamp' => $channel_response['engagement']['timestamp'],
        ]);
    }

    /** @test */
    public function it_can_send_a_notification_from_default_address()
    {
        $this->mockHubspotRequest();

        $channel_response = $this->channel->send(new TestNotifiable(), new TestLineMailNotification());
        $this->assertEquals($channel_response['metadata']['from'], [
            'email' => 'from@email.com',
            'firstName' => 'from_name',
        ]);
    }

    /** @test */
    public function it_can_send_a_notification_from_custom_address_without_name()
    {
        $this->mockHubspotRequest();

        $channel_response = $this->channel->send(new TestNotifiable(), new TestMarkdownMailNotification());
        $this->assertEquals($channel_response['metadata']['from'], [
            'email' => 'from2@email.com',
        ]);
    }

    /** @test */
    public function it_can_send_a_notification_from_custom_address_with_name()
    {
        $this->mockHubspotRequest();

        $channel_response = $this->channel->send(new TestNotifiable(), new TestViewMailNotification());
        $this->assertEquals($channel_response['metadata']['from'], [
            'email' => 'from3@email.com',
            'firstName' => 'From3',
        ]);
    }

    /** @test */
    public function it_can_send_a_notification_to_correct_email()
    {
        $this->mockHubspotRequest();

        $channel_response = $this->channel->send(new TestNotifiable(), new TestLineMailNotification());
        $this->assertEquals($channel_response['metadata']['to'], [[
            'email' => 'email@email.com',
        ]]);
    }

    /** @test */
    public function it_can_send_a_notification_with_empty_cc_bcc()
    {
        $this->mockHubspotRequest();

        $channel_response = $this->channel->send(new TestNotifiable(), new TestLineMailNotification());
        $this->assertEquals($channel_response['metadata']['cc'], []);
        $this->assertEquals($channel_response['metadata']['bcc'], []);
    }

    /** @test */
    public function it_can_send_a_notification_with_one_cc_bcc()
    {
        $this->mockHubspotRequest();

        $channel_response = $this->channel->send(new TestNotifiable(), new TestViewMailNotification());

        $this->assertEquals($channel_response['metadata']['cc'], [['email' => 'cc@email.com', 'firstName' => 'cc_name']]);
        $this->assertEquals($channel_response['metadata']['bcc'], [['email' => 'bcc@email.com', 'firstName' => 'bcc_name']]);
    }

    /** @test */
    public function it_can_send_a_notification_with_multiple_cc_bcc()
    {
        $this->mockHubspotRequest();

        $channel_response = $this->channel->send(new TestNotifiable(), new TestMarkdownMailNotification());

        $this->assertEquals($channel_response['metadata']['cc'], [
            ['email' => 'cc@email.com', 'firstName' => 'cc_name'],
            ['email' => 'cc2@email.com'],
        ]);
        $this->assertEquals($channel_response['metadata']['bcc'], [
            ['email' => 'bcc@email.com'],
            ['email' => 'bcc2@email.com', 'firstName' => 'bcc2_name'],
        ]);
    }

    /** @test */
    public function it_can_send_a_notification_with_line_email()
    {
        $this->mockHubspotRequest();

        $channel_response = $this->channel->send(new TestNotifiable(), new TestLineMailNotification());
        $this->assertIsArray($channel_response);
        $this->assertInstanceOf(\Illuminate\Support\HtmlString::class, $channel_response['metadata']['html']);
        $htmlString = (string) $channel_response['metadata']['html'];
        $this->assertStringContainsString('Greeting', $htmlString);
        $this->assertStringContainsString('Line', $htmlString);
        $this->assertStringContainsString('button', $htmlString);
        $this->assertStringContainsString('https://www.google.it', $htmlString);
        $this->assertEquals($channel_response['metadata']['subject'], 'Subject');
    }

    /** @test */
    public function it_can_send_a_notification_with_view_email()
    {
        $this->mockHubspotRequest();

        $channel_response = $this->channel->send(new TestNotifiable(), new TestViewMailNotification());

        $this->assertIsArray($channel_response);
        $this->assertIsString($channel_response['metadata']['html']);
        $this->assertEquals($channel_response['metadata']['subject'], 'Subject');
        $this->assertStringContainsString('Test View Content', $channel_response['metadata']['html']);
    }

    /** @test */
    public function it_can_send_a_notification_with_markdown_email_and_multiple_cc_bcc_and_different_from_without_name()
    {
        $this->mockHubspotRequest();
        $channel_response = $this->channel->send(new TestNotifiable(), new TestMarkdownMailNotification());

        $this->assertIsArray($channel_response);
        $this->assertInstanceOf(\Illuminate\Support\HtmlString::class, $channel_response['metadata']['html']);
        $htmlString = (string) $channel_response['metadata']['html'];
        $this->assertStringContainsString('Markdown Title Content', $htmlString);
        $this->assertStringContainsString('Markdown body content', $htmlString);
    }
}
