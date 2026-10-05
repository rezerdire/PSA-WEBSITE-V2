<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationEmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verify your PSA Convention registration email');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-email-verification',
            with: ['code' => $this->code],
        );
    }
}
