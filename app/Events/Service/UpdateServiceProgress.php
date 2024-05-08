<?php

namespace App\Events\Service;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

//Atualiza na tela de atendimento os atendimentos em progresso (Autoatendimento, Pendentes e Em Atendimento)
class UpdateServiceProgress implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userIdNotification;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $userIdNotification)
    {
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
        return 'UpdateServiceProgress';
    }

    //O que vai ser retornado no broadcast  
    public function broadcastWith()
    {
        return [
        ];
    }
}
