<?php

namespace App\Jobs;

use App\Mail\RegistrationQrCode;
use App\Models\Member;
use App\Models\MemberQr;
use App\Models\RegistrationQrBatch;
use App\Models\RegistrationQrBatchRecipient;
use App\Services\MemberQrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SendRegistrationQrBatchRecipient implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(
        public int $batchId,
        public int $recipientId,
    ) {
    }

    public function handle(MemberQrService $qrService): void
    {
        $recipient = RegistrationQrBatchRecipient::with(['batch', 'registration'])
            ->find($this->recipientId);

        if (! $recipient || $recipient->batch_id !== $this->batchId || $recipient->status !== RegistrationQrBatchRecipient::STATUS_PENDING) {
            return;
        }

        $batch = $recipient->batch;
        $registration = $recipient->registration;

        $recipient->update([
            'status' => RegistrationQrBatchRecipient::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        $batch->update([
            'status' => RegistrationQrBatch::STATUS_RUNNING,
            'started_at' => $batch->started_at ?? now(),
            'current_registration_id' => $recipient->registration_id,
            'current_name' => $recipient->name,
            'current_email' => $recipient->email,
            'current_status' => RegistrationQrBatchRecipient::STATUS_PROCESSING,
        ]);

        try {
            if (! $registration) {
                throw new \RuntimeException('Registration no longer exists.');
            }

            if (! $registration->isApproved()) {
                $this->finishRecipient(
                    $recipient,
                    RegistrationQrBatchRecipient::STATUS_SKIPPED,
                    'Skipped because the registration is no longer Approved.',
                );

                return;
            }

            if ($registration->qrSends()->exists()) {
                $this->finishRecipient(
                    $recipient,
                    RegistrationQrBatchRecipient::STATUS_SKIPPED,
                    'Skipped because a QR email was sent before this queued item ran.',
                );

                return;
            }

            $email = trim((string) $registration->email);

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Invalid or missing email address.');
            }

            $member = Member::find($registration->psa_id);

            if (! $member) {
                throw new \RuntimeException('No member record exists for this registration.');
            }

            $qrService->generate($member);

            $qr = MemberQr::where('member_id_no', $member->member_id_no)->first();
            $hasIdCard = $qr && Storage::disk('members_qr')->exists($qr->qr_path);

            if (! $qr || ! $hasIdCard) {
                throw new \RuntimeException('QR/ID card was not generated; email not sent.');
            }

            Mail::to($email)->send(new RegistrationQrCode($registration, true));
            $registration->qrSends()->create(['sent_at' => now()]);

            $this->finishRecipient($recipient, RegistrationQrBatchRecipient::STATUS_SENT);
        } catch (Throwable $exception) {
            $this->finishRecipient(
                $recipient,
                RegistrationQrBatchRecipient::STATUS_FAILED,
                mb_substr($exception->getMessage() ?: $exception::class, 0, 65000),
            );
        }
    }

    protected function finishRecipient(
        RegistrationQrBatchRecipient $recipient,
        string $status,
        ?string $errorMessage = null,
    ): void {
        $now = now();
        $recipient->update([
            'status' => $status,
            'error_message' => $errorMessage,
            'sent_at' => $status === RegistrationQrBatchRecipient::STATUS_SENT ? $now : null,
            'failed_at' => $status === RegistrationQrBatchRecipient::STATUS_FAILED ? $now : null,
        ]);

        $batch = RegistrationQrBatch::find($this->batchId);

        if (! $batch) {
            return;
        }

        $batch->increment('processed_count');

        if ($status === RegistrationQrBatchRecipient::STATUS_SENT) {
            $batch->increment('sent_count');
        } elseif ($status === RegistrationQrBatchRecipient::STATUS_FAILED) {
            $batch->increment('failed_count');
        } elseif ($status === RegistrationQrBatchRecipient::STATUS_SKIPPED) {
            $batch->increment('skipped_count');
        }

        $batch->update([
            'current_registration_id' => $recipient->registration_id,
            'current_name' => $recipient->name,
            'current_email' => $recipient->email,
            'current_status' => $status,
        ]);
    }
}
