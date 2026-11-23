<?php

namespace App\Services;

use App\Jobs\SendUnregisteredPsaId;
use App\Mail\UnregisteredPsaId;
use App\Models\UnregisteredPsaRecipient;
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

    public function queuePending(int $limit): int
    {
        if ($limit < 1 || $limit > 500) {
            throw new RuntimeException('The bulk send limit must be between 1 and 500.');
        }

        $recipientIds = DB::transaction(function () use ($limit): array {
            $recipients = UnregisteredPsaRecipient::query()
                ->where('status', 'pending')
                ->orderBy('id')
                ->lockForUpdate()
                ->limit($limit)
                ->get(['id']);

            $ids = $recipients->modelKeys();
            if ($ids !== []) {
                UnregisteredPsaRecipient::query()->whereIn('id', $ids)->update([
                    'status' => 'queued',
                    'error' => null,
                    'updated_at' => now(),
                ]);
            }

            return $ids;
        });

        if ($recipientIds === []) {
            throw new RuntimeException('There are no pending recipients to queue.');
        }

        try {
            $jobs = array_map(
                fn (int $id): SendUnregisteredPsaId => new SendUnregisteredPsaId($id),
                $recipientIds,
            );
            Bus::chain($jobs)->dispatch();
        } catch (\Throwable $exception) {
            UnregisteredPsaRecipient::query()->whereIn('id', $recipientIds)->where('status', 'queued')->update([
                'status' => 'pending',
                'updated_at' => now(),
            ]);

            throw $exception;
        }

        return count($recipientIds);
    }

    public function processQueuedRecipient(int $recipientId): void
    {
        $recipient = UnregisteredPsaRecipient::query()
            ->whereKey($recipientId)
            ->where('status', 'queued')
            ->first();

        if (! $recipient) {
            return;
        }

        $recipient->update(['status' => 'processing']);
        $this->sendRecipient($recipient);
    }

    public function sendOne(UnregisteredPsaRecipient $recipient): bool
    {
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
