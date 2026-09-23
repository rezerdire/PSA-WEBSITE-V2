<?php

namespace App\Mail;

use App\Models\Member;
use App\Models\MemberQr;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class RegistrationStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Registration $registration) {}

    public function build()
    {
        $subject = match ($this->registration->status) {
            Registration::STATUS_APPROVED => 'Your PSA Convention Registration has been Approved',
            Registration::STATUS_REJECTED => 'Update on Your PSA Convention Registration',
            default => 'PSA Convention Registration Update',
        };

        $qr = $this->getQr();
        $hasQrFile = $qr && Storage::disk('members_qr')->exists($qr->qr_path);

        // Embed the QR/ID card image directly as a base64 data URI so it
        // renders in the recipient's inbox regardless of where the app is
        // hosted (local, staging, production) — no external URL needed.
        $idCardUrl = null;
        if ($hasQrFile) {
            $binary = Storage::disk('members_qr')->get($qr->qr_path);
            $idCardUrl = 'data:image/png;base64,' . base64_encode($binary);
        }

        $mail = $this->subject($subject)->view('emails.registration-status-updated', [
            'registration' => $this->registration,
            'hasIdCard'    => $hasQrFile,
            'idCardUrl'    => $idCardUrl,
        ]);

        if ($hasQrFile) {
            $mail->attach(
                Attachment::fromStorageDisk('members_qr', $qr->qr_path)
                    ->as("PSA_ID_{$this->registration->psa_id}.png")
                    ->withMime('image/png')
            );
        }

        return $mail;
    }

    protected function getQr(): ?MemberQr
    {
        if ($this->registration->status !== Registration::STATUS_APPROVED) {
            return null;
        }

        $member = Member::find($this->registration->psa_id);

        if (! $member) {
            return null;
        }

        return MemberQr::where('member_id_no', $member->member_id_no)->first();
    }
}