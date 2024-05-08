<?php

namespace App\Events\Chat;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateService implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userIdNotification;
    public $serviceData;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $userIdNotification, array $serviceData)
    {
        $this->userIdNotification = $userIdNotification;
        $this->serviceData = $serviceData;

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
        return 'UpdateService';
    }

    //O que vai ser retornado no broadcast  
    public function broadcastWith()
    {
        return [
            'serviceData' => $this->serviceData
        ];
    }
}
