<?php

namespace App\Events\Api;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StatusConnection implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $status;
    public $session;
    public $userIdNotification;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($status, $session, int $userIdNotification)
    {
        $this->status = $status;
        $this->session = $session;
        $this->userIdNotification = $userIdNotification;

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
        return 'StatusConnection';
    }

    //O que vai ser retornado no broadcast  
    public function broadcastWith()
    {
        return [
            'status' => $this->status,
            'session' => $this->session
        ];
    }
}
