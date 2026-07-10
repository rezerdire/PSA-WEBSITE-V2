<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\MemberHospital;
use Illuminate\Database\Seeder;

class MemberHospitalSeeder extends Seeder
{
    /**
     * Expected CSV headers (case-sensitive, exact match):
     * member_id_no, hospital, hosp_address, hosp_hours, hosp_tel_no,
     * hosp_designation, hosp_days, hosp_remarks, hosp_primary
     *
     * hosp_primary accepts 1/0, true/false, yes/no (case-insensitive).
     *
     * Column length caps (per member_hospitals migration):
     * hospital(149), hosp_address(199), hosp_hours(50), hosp_tel_no(50),
     * hosp_designation(50), hosp_days(50), hosp_remarks(50).
     * Rows exceeding these are truncated with a warning (not silently dropped).
     *
     * If a row references a member_id_no NOT found in `members`, a minimal
     * placeholder Member is auto-created (psa_mem_type = 'UNK') so the FK
     * constraint is satisfied and the hospital row is NOT skipped. These
     * placeholder members are reported at the end and MUST be backfilled
     * with real data (or removed) once the correct member record is known.
     *
     * Place file at: database/seeders/csv/member_hospitals.csv
     */
    private const MAX_LENGTHS = [
        'hospital'         => 149,
        'hosp_address'     => 199,
        'hosp_hours'       => 50,
        'hosp_tel_no'      => 50,
        'hosp_designation' => 50,
        'hosp_days'        => 50,
        'hosp_remarks'     => 50,
    ];

    public function run(): void
    {
        $path = database_path('seeders/csv/member_hospitals.csv');

        if (! file_exists($path)) {
            $this->command->warn("Skipped: {$path} not found.");
            return;
        }

        $validMemberIds = Member::pluck('member_id_no')->flip();

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $header = array_map(fn ($h) => trim(str_replace("\xEF\xBB\xBF", '', $h)), $header);

        $rows = [];
        $now = now();
        $truncatedWarnings = 0;
        $missingMemberWarnings = 0;
        $placeholdersCreated = [];

        while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($header, $data);

            $memberIdNo = trim($row['member_id_no'] ?? '');

            if ($memberIdNo === '') {
                $missingMemberWarnings++;
                continue;
            }

            // Auto-create a minimal placeholder Member if it doesn't exist,
            // so the FK constraint is satisfied and the hospital row isn't skipped.
            if (! $validMemberIds->has($memberIdNo)) {
                Member::create([
                    'member_id_no'     => $memberIdNo,
                    'psa_chapter_code' => 'UNK',
                    'psa_mem_type'     => 'UNK',
                    'mem_last_name'    => 'PLACEHOLDER',
                    'mem_first_name'   => 'PLACEHOLDER',
                ]);

                $validMemberIds->put($memberIdNo, true);
                $placeholdersCreated[] = $memberIdNo;
            }

            $entry = [
                'member_id_no'      => $memberIdNo,
                'hosp_primary'      => $this->parseBool($row['hosp_primary'] ?? null),
                'created_at'        => $now,
                'updated_at'        => $now,
            ];

            foreach (self::MAX_LENGTHS as $field => $max) {
                $value = $this->nullable($row[$field] ?? null);

                if ($value !== null && strlen($value) > $max) {
                    $value = substr($value, 0, $max);
                    $truncatedWarnings++;
                }

                $entry[$field] = $value;
            }

            $rows[] = $entry;
        }

        fclose($handle);

        collect($rows)->chunk(500)->each(function ($chunk) {
            MemberHospital::upsert(
                $chunk->all(),
                ['member_id_no', 'hosp_primary', 'hospital', 'hosp_address'],
                [
                    'hosp_hours', 'hosp_designation', 'hosp_days',
                    'hosp_remarks', 'updated_at',
                ]
            );
        });

        $this->command->info('Member hospitals seeded: ' . count($rows));

        if ($truncatedWarnings > 0) {
            $this->command->warn("{$truncatedWarnings} field value(s) exceeded their column length and were truncated.");
        }

        if ($missingMemberWarnings > 0) {
            $this->command->warn("{$missingMemberWarnings} row(s) skipped due to missing member_id_no.");
        }

        if (! empty($placeholdersCreated)) {
            $this->command->warn(count($placeholdersCreated) . ' placeholder Member record(s) auto-created (psa_mem_type = UNK): ' . implode(', ', $placeholdersCreated));
            $this->command->warn('These MUST be reviewed and updated with real member data.');
        }
    }

    private function nullable(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function parseBool(?string $value): bool
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, ['1', 'true', 'yes'], true);
    }
}