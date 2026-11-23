<?php

namespace App\Services;

use App\Jobs\FinalizeUnregisteredPsaSendBatch;
use App\Jobs\SendUnregisteredPsaId;
use App\Mail\UnregisteredPsaId;
use App\Models\UnregisteredPsaRecipient;
use App\Models\UnregisteredPsaSendBatch;
use App\Models\UnregisteredPsaSendBatchRecipient;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class UnregisteredPsaRecipientService
{
    public function import(string $path): int
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Could not read the uploaded CSV.');
        }

        try {
            $header = fgetcsv($handle);
            if (! is_array($header)) {
                throw new RuntimeException('The CSV is empty. Required columns: psa_id, email; optional: last_name.');
            }
            $header = array_map(static function ($value): string {
                $value = strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $value)));

                return match ($value) {
                    'psa id' => 'psa_id',
                    'full name' => 'full_name',
                    'first name' => 'first_name',
                    'middle name' => 'middle_name',
                    'last name' => 'last_name',
                    'e-mail' => 'email',
                    default => $value,
                };
            }, $header);
            $psaIndex = array_search('psa_id', $header, true);
            $emailIndex = array_search('email', $header, true);
            $nameIndex = array_search('full_name', $header, true);
            $firstIndex = array_search('first_name', $header, true);
            $middleIndex = array_search('middle_name', $header, true);
            $lastIndex = array_search('last_name', $header, true);
            $chapterIndex = array_search('chapter', $header, true);
            if ($psaIndex === false || $emailIndex === false) {
                throw new RuntimeException('CSV must contain psa_id and email columns. last_name is optional.');
            }

            $rows = [];
            while (($row = fgetcsv($handle)) !== false) {
                $psaId = trim((string) ($row[$psaIndex] ?? ''));
                $email = trim((string) ($row[$emailIndex] ?? ''));
                if ($psaId === '' || $email === '' || strtoupper($email) === 'N/A') {
                    continue;
                }
                $fullName = $nameIndex === false ? null : (trim((string) ($row[$nameIndex] ?? '')) ?: null);
                $splitName = UnregisteredPsaRecipient::splitName($fullName);
                $firstName = $firstIndex === false ? null : (trim((string) ($row[$firstIndex] ?? '')) ?: null);
                $middleName = $middleIndex === false ? null : (trim((string) ($row[$middleIndex] ?? '')) ?: null);
                $lastName = $lastIndex === false ? null : (trim((string) ($row[$lastIndex] ?? '')) ?: null);
                $chapter = $chapterIndex === false ? null : (trim((string) ($row[$chapterIndex] ?? '')) ?: null);
                $firstName ??= $splitName['first_name'];
                $middleName ??= $splitName['middle_name'];
                $lastName ??= $splitName['last_name'];
                $fullName ??= implode(' ', array_filter([$firstName, $middleName, $lastName]));
                $rows[$psaId] = [
                    'psa_id' => $psaId,
                    'email' => $email,
                    'full_name' => $fullName ?: null,
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'chapter' => $chapter,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }
            if ($rows === []) {
                throw new RuntimeException('The CSV contains no rows with both psa_id and email.');
            }
            DB::transaction(function () use ($rows): void {
                foreach (array_chunk(array_values($rows), 500) as $chunk) {
                    DB::table('unregistered_psa_recipients')->upsert(
                        $chunk,
                        ['psa_id'],
                        ['email', 'full_name', 'first_name', 'middle_name', 'last_name', 'chapter', 'updated_at'],
                    );
                }
            });

            return count($rows);
        } finally {
            fclose($handle);
        }
    }

    public function queuePending(int $limit): UnregisteredPsaSendBatch
    {
        if ($limit < 1 || $limit > 500) {
            throw new RuntimeException('The bulk send limit must be between 1 and 500.');
        }

        $batch = DB::transaction(function () use ($limit): ?UnregisteredPsaSendBatch {
            $recipients = UnregisteredPsaRecipient::query()
                ->where('status', 'pending')
                ->orderBy('id')
                ->lockForUpdate()
                ->limit($limit)
                ->get();

            if ($recipients->isEmpty()) {
                return null;
            }

            $batch = UnregisteredPsaSendBatch::create([
                'status' => UnregisteredPsaSendBatch::STATUS_QUEUED,
                'requested_limit' => $limit,
                'total_count' => $recipients->count(),
                'created_by' => auth()->id(),
            ]);

            foreach ($recipients->values() as $index => $recipient) {
                $batch->recipients()->create([
                    'recipient_id' => $recipient->id,
                    'position' => $index + 1,
                    'psa_id' => $recipient->psa_id,
                    'name' => $recipient->full_name ?: trim(implode(' ', array_filter([
                        $recipient->first_name,
                        $recipient->middle_name,
                        $recipient->last_name,
                    ]))),
                    'email' => $recipient->email,
                    'chapter' => $recipient->chapter,
                    'status' => UnregisteredPsaSendBatchRecipient::STATUS_PENDING,
                ]);
                $recipient->update(['status' => 'queued', 'error' => null]);
            }

            return $batch;
        });

        if (! $batch) {
            throw new RuntimeException('There are no pending recipients to queue.');
        }

        try {
            $jobs = $batch->recipients()
                ->orderBy('position')
                ->get(['id'])
                ->map(fn (UnregisteredPsaSendBatchRecipient $item): SendUnregisteredPsaId => new SendUnregisteredPsaId($batch->id, $item->id))
                ->all();
            $jobs[] = new FinalizeUnregisteredPsaSendBatch($batch->id);
            Bus::chain($jobs)->dispatch();
        } catch (\Throwable $exception) {
            DB::transaction(function () use ($batch, $exception): void {
                $batch->recipients()->where('status', UnregisteredPsaSendBatchRecipient::STATUS_PENDING)->update([
                    'status' => UnregisteredPsaSendBatchRecipient::STATUS_FAILED,
                    'error_message' => mb_substr($exception->getMessage(), 0, 5000),
                    'failed_at' => now(),
                    'updated_at' => now(),
                ]);
                UnregisteredPsaRecipient::query()
                    ->whereIn('id', $batch->recipients()->whereNotNull('recipient_id')->pluck('recipient_id'))
                    ->where('status', 'queued')
                    ->update(['status' => 'pending', 'updated_at' => now()]);
                $batch->update([
                    'status' => UnregisteredPsaSendBatch::STATUS_QUEUE_FAILED,
                    'failed_count' => $batch->total_count,
                    'processed_count' => $batch->total_count,
                    'error_message' => mb_substr($exception->getMessage(), 0, 5000),
                    'finished_at' => now(),
                ]);
            });

            throw $exception;
        }

        return $batch;
    }

    public function processQueuedRecipient(int $batchId, int $batchRecipientId): void
    {
        $item = UnregisteredPsaSendBatchRecipient::query()
            ->where('batch_id', $batchId)
            ->whereKey($batchRecipientId)
            ->where('status', UnregisteredPsaSendBatchRecipient::STATUS_PENDING)
            ->first();

        if (! $item) {
            return;
        }

        $recipient = $item->recipient;
        $item->update([
            'status' => UnregisteredPsaSendBatchRecipient::STATUS_PROCESSING,
            'started_at' => now(),
        ]);
        $item->batch->update([
            'status' => UnregisteredPsaSendBatch::STATUS_RUNNING,
            'current_recipient_id' => $recipient?->id,
            'current_psa_id' => $item->psa_id,
            'current_name' => $item->name,
            'current_email' => $item->email,
            'current_status' => 'processing',
            'started_at' => $item->batch->started_at ?? now(),
        ]);

        if (! $recipient || $recipient->status !== 'queued') {
            $success = false;
            $error = $recipient
                ? 'Recipient was no longer queued when this job ran.'
                : 'Recipient record was deleted after the batch was queued.';
        } else {
            $recipient->update(['status' => 'processing']);
            $success = $this->sendRecipient($recipient);
            $error = $success ? null : $recipient->fresh()->error;
        }

        $now = now();
        $item->update([
            'status' => $success ? UnregisteredPsaSendBatchRecipient::STATUS_SENT : UnregisteredPsaSendBatchRecipient::STATUS_FAILED,
            'error_message' => $error,
            'sent_at' => $success ? $now : null,
            'failed_at' => $success ? null : $now,
        ]);

        $batch = $item->batch;
        $batch->increment('processed_count');
        $batch->increment($success ? 'sent_count' : 'failed_count');
        $batch->update(['current_status' => $success ? 'sent' : 'failed']);
    }

    public function finalizeBatch(int $batchId): void
    {
        $batch = UnregisteredPsaSendBatch::query()->find($batchId);
        if (! $batch || in_array($batch->status, [UnregisteredPsaSendBatch::STATUS_COMPLETED, UnregisteredPsaSendBatch::STATUS_COMPLETED_WITH_FAILURES, UnregisteredPsaSendBatch::STATUS_QUEUE_FAILED], true)) {
            return;
        }

        $batch->update([
            'status' => $batch->failed_count > 0
                ? UnregisteredPsaSendBatch::STATUS_COMPLETED_WITH_FAILURES
                : UnregisteredPsaSendBatch::STATUS_COMPLETED,
            'finished_at' => now(),
            'current_status' => 'completed',
        ]);
    }

    public function sendOne(UnregisteredPsaRecipient $recipient): bool
    {
        if (in_array($recipient->status, ['queued', 'processing'], true)) {
            return false;
        }

        return $this->sendRecipient($recipient);
    }

    private function sendRecipient(UnregisteredPsaRecipient $recipient): bool
    {
        try {
            $addresses = preg_split('/\\s*[\\/,]\\s*/', $recipient->email, -1, PREG_SPLIT_NO_EMPTY);
            Mail::to($addresses)->send(new UnregisteredPsaId($recipient->psa_id, $recipient->last_name));
            $recipient->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);

            return true;
        } catch (\Throwable $exception) {
            $recipient->update(['status' => 'failed', 'error' => mb_substr($exception->getMessage(), 0, 5000)]);

            return false;
        }
    }
}
