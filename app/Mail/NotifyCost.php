<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyCost extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $customerName;
    public $resourceDescription;
    public $typeResource;
    public $subject;

    public function __construct($customerName, $resourceDescription, int $typeResource, String $subject)
    {
        $this->customerName = $customerName;
        $this->resourceDescription = $resourceDescription;
        $this->typeResource = $typeResource;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->view('financial.cost.template_mail.notify_cost')
                    ->with($this->customerName, $this->resourceDescription, $this->typeResource);
    }
}
