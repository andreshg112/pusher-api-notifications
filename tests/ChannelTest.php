<?php

namespace Andreshg112\PusherApiNotifications\Test;

use Andreshg112\PusherApiNotifications\Exceptions\CouldNotSendNotification;
use Andreshg112\PusherApiNotifications\PusherApiChannel;
use Andreshg112\PusherApiNotifications\PusherApiMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery;
use Orchestra\Testbench\TestCase;

class ChannelTest extends TestCase
{
    /** @var \Pusher */
    protected $pusher = null;

    /** @var PusherApiChannel */
    protected $channel = null;

    /** @var TestNotification */
    protected $notification = null;

    /** @var TestNotifiable */
    protected $notifiable = null;

    public function setUp(): void
    {
        $this->pusher = Mockery::mock(\Pusher::class);
        $this->channel = new PusherApiChannel($this->pusher);
        $this->notification = new TestNotification;
        $this->notifiable = new TestNotifiable;

        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );

        Mockery::close();

        parent::tearDown();
    }

    /** @test */
    public function it_can_send_a_notification()
    {
        /** @var PusherApiMessage $message */
        $message = $this->notification->toApiNotification($this->notifiable);

        $data = $message->toArray();

        $this->pusher->shouldReceive('trigger')->with(
            $data['channels'],
            $data['event'],
            $data['data'],
            $data['socketId'],
            $data['debug'],
            $data['alreadyEncoded']
        )->andReturn(true);

        $this->channel->send($this->notifiable, $this->notification);
    }

    /** @test */
    public function it_cannot_send_a_notification()
    {
        $this->expectException(CouldNotSendNotification::class);

        /** @var PusherApiMessage $message */
        $message = $this->notification->toApiNotification($this->notifiable);

        $data = $message->toArray();

        $this->pusher->shouldReceive('trigger')->with(
            $data['channels'],
            $data['event'],
            $data['data'],
            $data['socketId'],
            $data['debug'],
            $data['alreadyEncoded']
        )->andReturn(false);

        $this->channel->send($this->notifiable, $this->notification);
    }
}

class TestNotifiable
{
    use Notifiable;
}

class TestNotification extends Notification
{
    public function toApiNotification($notifiable)
    {
        return new PusherApiMessage();
    }
}
