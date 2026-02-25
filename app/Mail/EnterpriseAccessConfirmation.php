<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnterpriseAccessConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(public array $data) {}

    public function build()
    {
        $logoPath = public_path('images/nri-logo.png');

        return $this->subject('We received your Enterprise Access request')
            ->view('emails.enterprise-access-confirmation')
            ->with([
                'logoPath' => $logoPath,
            ]);
    }
}
