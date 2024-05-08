<?php

namespace App\Events\Management\User;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendAlertNotification implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $userIdNotification;
    public $senderName;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message, $userIdNotification, $senderName)
    {
        $this->message = $message;
        $this->userIdNotification = $userIdNotification;
        $this->senderName = $senderName;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userIdNotification);
    }

    //Apelido do broadcast
    public function broadcastAs()
    {
        return 'SendAlertNotification';
    }

    //O que vai ser retornado no broadcast  
    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'userName' => $this->senderName
        ];
    }
}
