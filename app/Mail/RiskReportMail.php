<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RiskReportMail extends Mailable
{
    use Queueable, SerializesModels;

    // =================================================================
    // CRITICAL FIX 1: Declare these as PUBLIC so the View sees them
    // =================================================================
    public $lga;
    public $year;

    // We keep the PDF content protected because we don't need to print binary data in the HTML
    protected $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct($pdfContent, $lga, $year)
    {
        $this->pdfContent = $pdfContent;
        $this->lga = $lga;
        $this->year = $year;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Risk Report: {$this->lga} ({$this->year})")
            // Ensure this View name matches your actual file name exactly
            ->view('emails.risk_report_template')
            ->attachData($this->pdfContent, "Risk_Report_{$this->lga}_{$this->year}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
