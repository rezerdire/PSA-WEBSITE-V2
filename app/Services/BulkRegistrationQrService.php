<?php

namespace App\Services;

use App\Jobs\FinalizeRegistrationQrBatch;
use App\Jobs\SendRegistrationQrBatchRecipient;
use App\Models\Registration;
use App\Models\RegistrationQrBatch;
use App\Models\RegistrationQrBatchRecipient;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BulkRegistrationQrService
{
    public function start(int $limit, ?int $createdBy = null): RegistrationQrBatch
    {
        if ($limit < 1 || $limit > 500) {
            throw new RuntimeException('The bulk QR limit must be between 1 and 500.');
        }

        /** @var RegistrationQrBatch $batch */
        $batch = DB::transaction(function () use ($limit, $createdBy): RegistrationQrBatch {
            $eligible = $this->eligibleQuery()->lockForUpdate();
            $eligibleCount = (clone $eligible)->count();
            $candidates = $eligible->limit($limit + 1)->get();

            if ($candidates->isEmpty()) {
                throw new RuntimeException('There are no approved registrations waiting for a QR email.');
            }

            $selected = $candidates->take($limit);
            $next = $candidates->get($limit);

            $batch = RegistrationQrBatch::create([
                'status' => RegistrationQrBatch::STATUS_QUEUED,
                'requested_limit' => $limit,
                'total_count' => $selected->count(),
                'not_selected_count' => max(0, $eligibleCount - $selected->count()),
                'created_by' => $createdBy,
            ]);

            $selected->values()->each(function (Registration $registration, int $index) use ($batch): void {
                $batch->recipients()->create([
                    'registration_id' => $registration->id,
                    'position' => $index + 1,
                    'name' => $registration->full_name,
                    'email' => $registration->email,
                    'status' => RegistrationQrBatchRecipient::STATUS_PENDING,
                ]);
            });

            if ($next instanceof Registration) {
                $batch->recipients()->create([
                    'registration_id' => $next->id,
                    'position' => $limit + 1,
                    'name' => $next->full_name,
                    'email' => $next->email,
                    'status' => RegistrationQrBatchRecipient::STATUS_NOT_SELECTED_LIMIT,
                    'error_message' => 'Not selected because the bulk send limit was reached.',
                ]);
            }

            return $batch;
        });

        $this->dispatch($batch);

        return $batch->fresh();
    }

    protected function eligibleQuery()
    {
        return Registration::query()
            ->approved()
            ->whereIn('membership', ['RM', 'LM', 'TM'])
            ->whereDoesntHave('qrSends')
            ->whereDoesntHave('bulkQrBatchRecipients', function ($query): void {
                $query->whereIn('status', [
                    RegistrationQrBatchRecipient::STATUS_PENDING,
                    RegistrationQrBatchRecipient::STATUS_PROCESSING,
                ]);
            })
            ->orderBy('created_at')
            ->orderBy('id');
    }

    protected function dispatch(RegistrationQrBatch $batch): void
    {
        $jobs = $batch->recipients()
            ->where('status', RegistrationQrBatchRecipient::STATUS_PENDING)
            ->orderBy('position')
            ->pluck('id')
            ->map(fn (int $recipientId) => new SendRegistrationQrBatchRecipient($batch->id, $recipientId))
            ->all();

        $jobs[] = new FinalizeRegistrationQrBatch($batch->id);

        Bus::chain($jobs)->dispatch();
    }
}
