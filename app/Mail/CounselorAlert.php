<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CounselorAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $alertTitle,
        public string $alertBody,
        public ?string $actionUrl = null,
        public string $actionLabel = 'Open Portal',
    ) {}

    public function build()
    {
        return $this->subject('[PSU Support] ' . $this->alertTitle)
            ->view('emails.counselor_alert');
    }
}
