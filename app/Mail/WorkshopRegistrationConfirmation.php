<?php

namespace App\Mail;

use App\Models\WorkshopRegistration;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WorkshopRegistrationConfirmation extends Mailable
{
    public function __construct(
        public WorkshopRegistration $registration,
        public string $workshopLabel,
        public array $workshopDetails = [],
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PSA Workshop Registration Confirmed',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.workshop-registration-confirmation',
            with: [
                'registration' => $this->registration,
                'name' => $this->registration->full_name,
                'workshop' => $this->workshopLabel,
                'session' => $this->workshopDetails['session'] ?? null,
                'venue' => $this->workshopDetails['venue'] ?? null,
            ],
        );
    }
}