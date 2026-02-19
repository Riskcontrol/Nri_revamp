<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function build()
    {
        // Optional logo embedding path (public/images/nri-logo.png)
        $logoPath = public_path('images/nri-logo.png');

        return $this->subject('Welcome to Nigeria’s Premier Security Intelligence')
            ->view('emails.welcome-email')
            ->with([
                'data' => $this->payload,
                'logoPath' => $logoPath,
            ]);
    }
}
