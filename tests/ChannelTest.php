<?php

namespace NotificationChannels\HubspotEngagement\Test;

use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;
use SevenShores\Hubspot\Factory as Hubspot;
use NotificationChannels\HubspotEngagement\HubspotEngagementChannel;

class ChannelTest extends TestCase
{
    /** @var Mockery\Mock */
    protected $hubspot;

    /** @var \NotificationChannels\HubspotEngagement\HubspotEngagementChannel */
    protected $channel;


    public function setUp(): void
    {
        parent::setUp();
        $this->hubspot = Mockery::mock(Hubspot::class);

        $this->channel = new HubspotEngagementChannel($this->hubspot, ["address" => "email@email.com", "name" => "name"]);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_send_a_notification()
    {
        $response = new Response(200);

        $this->hubspot->shouldReceive('create')
            ->once()
            ->with([
                'engagement' => [
                    "active" => true,
                    "ownerId" => 123456789,
                    "type" => "EMAIL",
                    "timestamp" => \Carbon\Carbon::now()->getPreciseTimestamp(3)
                ],
                'associations' => [
                    "contactIds" => 987654321,
                    "companyIds" => [],
                    "dealIds" => [],
                    "ownerIds" => [],
                    "ticketIds" => []
                ],
                'metadata' => [
                    "from" => [
                        "email" => 'email@email.com',
                        "firstName" => 'name'
                    ],
                    "to" => [[
                        "email" => 'email@email'
                    ]],
                    "cc" => [],
                    "bcc" => [],
                    "subject" => 'Subject',
                    "html" => '<h1>Greeting</h1><p>Line</p><p><a href="https://www.google.it">button</a></p>'
                ],
                'attachments' => [],
            ])
            ->andReturn($response);

        $channel_response = $this->channel->send(new Notifiable(), new TestLineMailNotification());

        $this->assertInstanceOf(ResponseInterface::class, $channel_response);
    }
//    public function it_throws_an_exception_when_service_config_missing(){
//
//    }

    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
