<?php

namespace Database\Seeders;

use App\Models\Chapter;
use Illuminate\Database\Seeder;

class ChapterSeeder extends Seeder
{
    /**
     * Expected CSV headers (case-sensitive, exact match):
     * psa_chapter_code, psa_chapter_desc, psa_chapter_address,
     * psa_chapter_president, psa_chapter_contact_no
     *
     * Column length caps (per chapters migration):
     * psa_chapter_code(3, primary key), psa_chapter_desc(50),
     * psa_chapter_address(100), psa_chapter_president(50),
     * psa_chapter_contact_no(50, required — not nullable in schema).
     * Rows exceeding these are truncated with a warning (not silently dropped).
     *
     * NOTHING IS SKIPPED:
     * - Missing psa_chapter_contact_no -> filled with 'N/A'.
     * - Missing/blank psa_chapter_code -> auto-assigned a placeholder code
     *   (XX1, XX2, ...) since it's the table's primary key and cannot be
     *   empty at the database level. These are flagged and MUST be
     *   reviewed and corrected with the real chapter code later.
     *
     * Place file at: database/seeders/csv/chapters.csv
     */
    private const MAX_LENGTHS = [
        'psa_chapter_code'       => 3,
        'psa_chapter_desc'       => 50,
        'psa_chapter_address'    => 100,
        'psa_chapter_president'  => 50,
        'psa_chapter_contact_no' => 50,
    ];

    public function run(): void
    {
        $path = database_path('seeders/csv/Chapters.csv');

        if (! file_exists($path)) {
            $this->command->warn("Skipped: {$path} not found.");
            return;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $header = array_map(fn ($h) => trim(str_replace("\xEF\xBB\xBF", '', $h)), $header);

        $rows = [];
        $truncatedWarnings = 0;
        $placeholderCodesAssigned = [];
        $usedCodes = Chapter::pluck('psa_chapter_code')->flip();
        $placeholderCounter = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($header, $data);

            $chapterCode = trim($row['psa_chapter_code'] ?? '');

            // psa_chapter_code is the primary key — cannot be empty at the
            // DB level, so auto-assign a placeholder instead of skipping.
            if ($chapterCode === '') {
                do {
                    $chapterCode = 'X' . str_pad((string) $placeholderCounter, 2, '0', STR_PAD_LEFT);
                    $placeholderCounter++;
                } while ($usedCodes->has($chapterCode));

                $usedCodes->put($chapterCode, true);
                $placeholderCodesAssigned[] = $chapterCode;
            } elseif (! $usedCodes->has($chapterCode)) {
                $usedCodes->put($chapterCode, true);
            }

            $contactNo = $this->nullable($row['psa_chapter_contact_no'] ?? null) ?? 'N/A';

            $entry = [
                'psa_chapter_code'       => $this->truncate($chapterCode, self::MAX_LENGTHS['psa_chapter_code'], $truncatedWarnings),
                'psa_chapter_desc'       => $this->truncate($this->nullable($row['psa_chapter_desc'] ?? null), self::MAX_LENGTHS['psa_chapter_desc'], $truncatedWarnings),
                'psa_chapter_address'    => $this->truncate($this->nullable($row['psa_chapter_address'] ?? null), self::MAX_LENGTHS['psa_chapter_address'], $truncatedWarnings),
                'psa_chapter_president'  => $this->truncate($this->nullable($row['psa_chapter_president'] ?? null), self::MAX_LENGTHS['psa_chapter_president'], $truncatedWarnings),
                'psa_chapter_contact_no' => $this->truncate($contactNo, self::MAX_LENGTHS['psa_chapter_contact_no'], $truncatedWarnings),
            ];

            $rows[] = $entry;
        }

        fclose($handle);

        collect($rows)->chunk(500)->each(function ($chunk) {
            Chapter::upsert(
                $chunk->all(),
                ['psa_chapter_code'],
                [
                    'psa_chapter_desc', 'psa_chapter_address',
                    'psa_chapter_president', 'psa_chapter_contact_no',
                ]
            );
        });

        $this->command->info('Chapters seeded: ' . count($rows));

        if ($truncatedWarnings > 0) {
            $this->command->warn("{$truncatedWarnings} field value(s) exceeded their column length and were truncated.");
        }

        if (! empty($placeholderCodesAssigned)) {
            $this->command->warn(count($placeholderCodesAssigned) . ' chapter(s) had blank psa_chapter_code — auto-assigned: ' . implode(', ', $placeholderCodesAssigned));
            $this->command->warn('These MUST be reviewed and corrected with the real chapter code.');
        }
    }

    private function nullable(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function truncate(?string $value, int $max, int &$warningCounter): ?string
    {
        if ($value !== null && strlen($value) > $max) {
            $warningCounter++;
            return substr($value, 0, $max);
        }

        return $value;
    }
}