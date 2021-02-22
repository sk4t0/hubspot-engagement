<?php

namespace NotificationChannels\HubspotEngagement\Test;

use Mockery;
use NotificationChannels\HubspotEngagement\Exceptions\InvalidConfiguration;
use Orchestra\Testbench\TestCase;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;
use SevenShores\Hubspot\Factory as Hubspot;
use NotificationChannels\HubspotEngagement\HubspotEngagementChannel;
use SevenShores\Hubspot\Resources\Engagements;

class ChannelFeaturesTest extends TestCase
{
    /** @var Mockery\Mock */
    protected $hubspot;

    /** @var \NotificationChannels\HubspotEngagement\HubspotEngagementChannel */
    protected $channel;

    /** @var Mockery\LegacyMockInterface|Mockery\MockInterface|Engagements  */
    protected $engagement;


    public function setUp(): void
    {
        parent::setUp();
        $this->hubspot = Mockery::mock(Hubspot::class);
        $this->engagement = Mockery::mock(Engagements::class);
        $this->hubspot->allows()->engagements()->andReturns($this->engagement);

        $this->channel = new HubspotEngagementChannel($this->hubspot);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_throws_an_exception_when_it_is_not_configured()
    {
        $this->expectException(InvalidConfiguration::class);

        $this->channel->send(new TestNotifiable(), new TestLineMailNotification());
    }

    /** @test */
//    public function it_can_send_a_notification()
//    {
//
//        $this->app['config']->set('mail.from.address', 'email@email.com');
//        $this->app['config']->set('mail.from.name', 'name');
//
//        $response = new Response(200);
//
//        $this->hubspot->shouldReceive('engagements')
//            ->once()
//            ->with([
//                'engagement' => [
//                    "active" => true,
//                    "ownerId" => 123456789,
//                    "type" => "EMAIL",
//                    "timestamp" => \Carbon\Carbon::now()->getPreciseTimestamp(3)
//                ],
//                'associations' => [
//                    "contactIds" => 987654321,
//                    "companyIds" => [],
//                    "dealIds" => [],
//                    "ownerIds" => [],
//                    "ticketIds" => []
//                ],
//                'metadata' => [
//                    "from" => [
//                        "email" => 'email@email.com',
//                        "firstName" => 'name'
//                    ],
//                    "to" => [[
//                        "email" => 'email@email'
//                    ]],
//                    "cc" => [],
//                    "bcc" => [],
//                    "subject" => 'Subject',
//                    "html" => '<h1>Greeting</h1><p>Line</p><p><a href="https://www.google.it">button</a></p>'
//                ],
//                'attachments' => [],
//            ])
//            ->andReturn($response);
//
//        $channel_response = $this->channel->send(new TestNotifiable(), new TestLineMailNotification());
//
//        $this->assertInstanceOf(ResponseInterface::class, $channel_response);
//    }
//    public function it_throws_an_exception_when_service_config_missing(){
//
//    }

    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
