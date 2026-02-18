<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnterpriseAccessConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data) {}

    public function build()
    {
        $logoPath = public_path('images/nri-logo.png');

        return $this->subject('We received your Enterprise Access request')
            ->view('emails.enterprise-access-confirmation')
            ->with([
                'logoPath' => $logoPath, // pass path; blade decides how to render
            ]);
    }
}
