<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;

class SendCompanyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $notificationData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($notificationData)
    {
        $this->notificationData = $notificationData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notificationController = app(\App\Http\Controllers\Administration\AdmNotificationController::class);
        //Cria um JOB para o envio de notificação para uma empresa
        $notificationController->sendNotificationRoutine($this->notificationData);
    }
}
