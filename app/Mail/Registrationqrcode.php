<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Registration;
use App\Models\Member;
use App\Models\MemberQr;
use Illuminate\Support\Facades\Storage;
class Registrationqrcode extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
            public Registration $registration,
            public bool $hasIdCard = false,
            public ?string $idCardUrl = null,
        ) 
    {

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PSA 58th Annual Convention Registration - QR ID',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-qr',
            with: [
                'registration' => $this->registration,
                'hasIdCard'    => $this->hasIdCard,
                'idCardUrl'    => $this->idCardUrl,
            ],
            );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
      public function attachments(): array
    {
        $qr = $this->getQr();

        if (! $qr || ! Storage::disk('members_qr')->exists($qr->qr_path)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('members_qr', $qr->qr_path)
                ->as("PSA_ID_{$this->registration->psa_id}.png")
                ->withMime('image/png'),
        ];
    }

    protected function getQr(): ?MemberQr
    {
        $member = Member::find($this->registration->psa_id);

        if (! $member) {
            return null;
        }

        return MemberQr::where('member_id_no', $member->member_id_no)->first();
    }
}
