<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;
    public Carbon $submittedAt;

    /**
     * @param array{name:string,email:string,phone?:string,topic?:string,message:string} $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->submittedAt = now();
    }

    public function build()
    {
        return $this
            ->subject('New contact form message from ' . $this->data['name'])
            // Lets the support team hit "Reply" and email the customer directly
            ->replyTo($this->data['email'], $this->data['name'])
            ->view('emails.contact')
            ->with([
                'data'        => $this->data,
                'submittedAt' => $this->submittedAt,
            ]);
    }
}