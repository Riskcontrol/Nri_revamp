<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries   = 3;   // retry up to 3 times before marking failed
    public int $backoff = 60;  // wait 60 seconds between retries

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function build()
    {
        $logoPath = public_path('images/nri-logo.png');

        return $this->subject('Welcome to Nigeria\'s Premier Security Intelligence')
            ->view('emails.welcome-email')
            ->with([
                'data'     => $this->payload,
                'logoPath' => $logoPath,
            ]);
    }
}
