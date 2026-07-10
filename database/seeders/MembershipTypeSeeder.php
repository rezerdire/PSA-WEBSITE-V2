<?php

namespace Database\Seeders;

use App\Models\MembershipType;
use Illuminate\Database\Seeder;

class MembershipTypeSeeder extends Seeder
{
    /**
     * Expected CSV headers (case-sensitive, exact match):
     * Memtypecode, Memtype, stat
     *
     * stat accepts 1/0, true/false, yes/no (case-insensitive).
     *
     * Column length cap (per membership__types migration):
     * Memtypecode(2, nullable). Memtype has no declared length limit in the
     * migration ($table->string('Memtype', 50)) — capped at 50 below.
     * Rows exceeding these are truncated with a warning (not silently dropped).
     *
     * NOTHING IS SKIPPED:
     * - Missing/blank Memtype -> filled with 'UNSPECIFIED'.
     * - Missing/blank stat -> defaults to false (inactive).
     * - Missing/blank Memtypecode -> allowed as-is (column is nullable,
     *   and there's no primary/unique constraint on it in the schema),
     *   but these rows are flagged since Member::membershipType() relies
     *   on Memtypecode matching Member::psa_mem_type to resolve.
     *
     * NOTE: membership__types.id is the real primary key (auto-increment).
     * Memtypecode has no unique constraint at the DB level, so this seeder
     * matches on Memtypecode in PHP (via updateOrCreate) rather than a
     * DB-level upsert, to avoid creating duplicate rows on re-seed.
     *
     * Place file at: database/seeders/csv/membership_types.csv
     */
    private const MAX_LENGTHS = [
        'Memtypecode' => 2,
        'Memtype'     => 50,
    ];

    public function run(): void
    {
        $path = database_path('seeders/csv/Membership_Type.csv');

        if (! file_exists($path)) {
            $this->command->warn("Skipped: {$path} not found.");
            return;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $header = array_map(fn ($h) => trim(str_replace("\xEF\xBB\xBF", '', $h)), $header);

        $count = 0;
        $truncatedWarnings = 0;
        $blankCodeWarnings = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($header, $data);

            $memtypecode = $this->truncate(
                $this->nullable($row['Memtypecode'] ?? null),
                self::MAX_LENGTHS['Memtypecode'],
                $truncatedWarnings
            );

            $memtype = $this->truncate(
                $this->nullable($row['Memtype'] ?? null) ?? 'UNSPECIFIED',
                self::MAX_LENGTHS['Memtype'],
                $truncatedWarnings
            );

            $stat = $this->parseBool($row['stat'] ?? null);

            if ($memtypecode === null) {
                $blankCodeWarnings++;
            }

            // Match on Memtypecode (PHP-side, no DB unique constraint exists)
            // when present; otherwise always create a new row since there's
            // no reliable key to match blank-code rows against.
            if ($memtypecode !== null) {
                MembershipType::updateOrCreate(
                    ['Memtypecode' => $memtypecode],
                    ['Memtype' => $memtype, 'stat' => $stat]
                );
            } else {
                MembershipType::create([
                    'Memtypecode' => null,
                    'Memtype'     => $memtype,
                    'stat'        => $stat,
                ]);
            }

            $count++;
        }

        fclose($handle);

        $this->command->info('Membership types seeded: ' . $count);

        if ($truncatedWarnings > 0) {
            $this->command->warn("{$truncatedWarnings} field value(s) exceeded their column length and were truncated.");
        }

        if ($blankCodeWarnings > 0) {
            $this->command->warn("{$blankCodeWarnings} row(s) had a blank Memtypecode — inserted as-is, but Member::membershipType() won't be able to match these to any member.");
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

    private function parseBool(?string $value): bool
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, ['1', 'true', 'yes'], true);
    }
}