<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SendUserNotification extends Notification implements ShouldBroadcast
{
    //use Queueable;

    private $title;
    private $message;
    private $imageUrl;
    private $typeNotification;
    private $senderName;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $title, string $message, $imageUrl, $typeNotification, $senderName)
    {
        $this->title = $title;
        $this->message = $message;
        $this->imageUrl = $imageUrl;
        $this->typeNotification = $typeNotification;
        $this->senderName = $senderName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'imageUrl' => $this->imageUrl,
            'typeNotification' => $this->typeNotification,
            'senderName' => $this->senderName,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => $this->title,
            'message' => $this->message,
            'imageUrl' => $this->imageUrl,
            'typeNotification' => $this->typeNotification,
            'senderName' => $this->senderName,
        ]);
    }
}
