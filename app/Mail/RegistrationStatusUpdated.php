<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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

        return $this->subject($subject)->markdown('emails.registration-status-updated');
    }
}