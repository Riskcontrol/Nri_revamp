<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnterpriseAccessAdminNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(public array $data) {}

    public function build()
    {
        return $this->subject('New Enterprise Access request submitted')
            ->view('emails.enterprise-access-admin');
    }
}
