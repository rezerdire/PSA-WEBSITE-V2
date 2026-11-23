<?php

namespace App\Mail;

use App\Services\PsaIdCardLocator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UnregisteredPsaId extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $psaId, public ?string $lastName = null)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'PSA 58th Annual Convention Registration - QR ID');
    }

    public function content(): Content
    {
        $hasIdCard = app(PsaIdCardLocator::class)->pathFor($this->psaId) !== null;

        return new Content(view: 'emails.registration-qr', with: [
            'registration' => (object) ['psa_id' => $this->psaId, 'last_name' => $this->lastName ?: 'Member'],
            'hasIdCard' => $hasIdCard,
            'idCardUrl' => null,
        ]);
    }

    public function attachments(): array
    {
        $path = app(PsaIdCardLocator::class)->pathFor($this->psaId);
        if ($path === null) {
            return [];
        }

        return [
            Attachment::fromPath($path)
                ->as("PSA_ID_{$this->psaId}." . pathinfo($path, PATHINFO_EXTENSION))
                ->withMime(mime_content_type($path) ?: 'application/octet-stream'),
        ];
    }
}
