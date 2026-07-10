<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MemberSeeder extends Seeder
{
    /**
     * Expected CSV headers (case-sensitive, exact match):
     * member_id_no, psa_chapter_code, psa_mem_type, mem_last_name, mem_first_name,
     * mem_middle_name, mem_home_address, mem_mobile_no1, mem_email_address,
     * mem_birth_date, mem_gender, mem_prc_no, mem_pma_id_no, mem_fellow_no, mem_phic_no
     *
     * mem_birth_date accepts common formats (YYYY-MM-DD, MM/DD/YYYY, etc.) via Carbon::parse.
     * Blank dates are stored as null.
     *
     * IMPORTANT: member_hospitals.member_id_no is constrained to string(4).
     * If any member_id_no here exceeds 4 characters, later hospital seeding
     * (or the FK itself, depending on your DB) will fail. This seeder warns
     * on any row that breaks that limit but does not truncate the value.
     *
     * Place file at: database/seeders/csv/members.csv
     */
    public function run(): void
    {
        $path = database_path('seeders/csv/members.csv');

        if (! file_exists($path)) {
            $this->command->warn("Skipped: {$path} not found.");
            return;
        }

        $handle = fopen($path, 'r');
$header = fgetcsv($handle);
$header = array_map(fn ($h) => trim(str_replace("\xEF\xBB\xBF", '', $h)), $header);
        $rows = [];
        $now = now();
        $longIdWarnings = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($header, $data);

            $memberIdNo = trim($row['member_id_no']);

            if (strlen($memberIdNo) > 4) {
                $longIdWarnings++;
            }

            $rows[] = [
                'member_id_no'       => $memberIdNo,
                'psa_chapter_code'   => trim($row['psa_chapter_code']),
                'psa_mem_type'       => trim($row['psa_mem_type']),
                'mem_last_name'      => trim($row['mem_last_name']),
                'mem_first_name'     => trim($row['mem_first_name']),
                'mem_middle_name'    => $this->nullable($row['mem_middle_name'] ?? null),
                'mem_home_address'   => $this->nullable($row['mem_home_address'] ?? null),
                'mem_mobile_no1'     => $this->nullable($row['mem_mobile_no1'] ?? null),
                'mem_email_address'  => $this->nullable($row['mem_email_address'] ?? null),
                'mem_birth_date'     => $this->parseDate($row['mem_birth_date'] ?? null),
                'mem_gender'         => $this->nullable($row['mem_gender'] ?? null),
                'mem_prc_no'         => $this->nullable($row['mem_prc_no'] ?? null),
                'mem_pma_id_no'      => $this->nullable($row['mem_pma_id_no'] ?? null),
                'mem_fellow_no'      => $this->nullable($row['mem_fellow_no'] ?? null),
                'mem_phic_no'        => $this->nullable($row['mem_phic_no'] ?? null),
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
        }

        fclose($handle);

        collect($rows)->chunk(500)->each(function ($chunk) {
            Member::upsert(
                $chunk->all(),
                ['member_id_no'],
                [
                    'psa_chapter_code', 'psa_mem_type', 'mem_last_name', 'mem_first_name',
                    'mem_middle_name', 'mem_home_address', 'mem_mobile_no1', 'mem_email_address',
                    'mem_birth_date', 'mem_gender', 'mem_prc_no', 'mem_pma_id_no',
                    'mem_fellow_no', 'mem_phic_no', 'updated_at',
                ]
            );
        });

        $this->command->info('Members seeded: ' . count($rows));

        if ($longIdWarnings > 0) {
            $this->command->warn("{$longIdWarnings} member_id_no value(s) exceed 4 characters — verify against member_hospitals.member_id_no's string(4) constraint.");
        }
    }

    private function nullable(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}