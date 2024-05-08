<?php

namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewChat extends Notification
{

    use Queueable;
    
    
    
    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Novo Chat Pendente')
            ->icon('/notification-icon.png')
            ->body('Existe um novo contato aguardando ser atendimento.')
            ->action('View App', 'notification_action');
    }
    
}