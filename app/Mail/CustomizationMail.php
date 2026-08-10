<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomizationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $product;
    public $savedFiles;

    public function __construct($product, $savedFiles)
    {
        $this->product    = $product;
        $this->savedFiles = $savedFiles;
    }

    public function build()
    {
        return $this->subject('Your Customized Product Added to Cart')
            ->view('emails.customization');
    }
}