<?php

namespace App\Http\Controllers\Admin;

use App\Models\RegistrationQrBatch;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationQrBatchExportController
{
    public function __invoke(RegistrationQrBatch $batch): StreamedResponse
    {
        $filename = "registration-qr-batch-{$batch->id}.csv";

        return response()->streamDownload(function () use ($batch): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'Batch ID',
                'Position',
                'Registration ID',
                'Name',
                'Email',
                'Status',
                'Error / Note',
                'Started At',
                'Sent At',
                'Failed At',
            ]);

            $batch->recipients()
                ->orderBy('position')
                ->cursor()
                ->each(function ($recipient) use ($output, $batch): void {
                    fputcsv($output, [
                        $batch->id,
                        $recipient->position,
                        $recipient->registration_id,
                        $recipient->name,
                        $recipient->email,
                        $recipient->status,
                        $recipient->error_message,
                        $recipient->started_at?->toDateTimeString(),
                        $recipient->sent_at?->toDateTimeString(),
                        $recipient->failed_at?->toDateTimeString(),
                    ]);
                });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
