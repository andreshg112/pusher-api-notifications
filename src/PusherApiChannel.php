<?php

namespace Andreshg112\PusherApiNotifications;

use Andreshg112\PusherApiNotifications\Exceptions\CouldNotSendNotification;
use Illuminate\Notifications\Notification;

class PusherApiChannel
{
    /** @var \Pusher|\Pusher\Pusher */
    protected $pusher;

    public function __construct(\Pusher $pusher)
    {
        $this->pusher = $pusher;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     *
     * @throws \Andreshg112\PusherApiNotifications\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        /** @var PusherApiMessage $pusherApiMessage */
        $pusherApiMessage = $notification->toApiNotification($notifiable);

        $message = $pusherApiMessage->toArray();

        $response = $this->pusher::trigger(
            $message['channels'],
            $message['event'],
            $message['data'],
            $message['socketId'] ?? null,
            $message['debug'] ?? false,
            $message['alreadyEncoded'] ?? false
        );

        if (! $response) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($response);
        }
    }
}
