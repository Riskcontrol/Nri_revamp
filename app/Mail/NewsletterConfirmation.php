<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(public NewsletterSubscriber $subscriber) {}

    public function build()
    {
        $logoPath = public_path('images/nri-logo.png');

        return $this->subject('Confirm your Nigeria Risk Index subscription')
            ->view('emails.newsletter-confirmation')
            ->with([
                'logoPath' => $logoPath,
            ]);
    }
}
