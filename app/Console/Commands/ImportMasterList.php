<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ImportMasterList extends Command
{
    protected $signature = 'masterlist:import
        {members=public/masterlist/member.csv : Member CSV path}
        {nonmembers=public/masterlist/nonmember.csv : Nonmember CSV path}';

    protected $description = 'Replace the unified evaluation master list from member and nonmember CSV files';

    private const SOURCES = [
        'members' => [
            'record_type' => 'member',
            'id' => 'member_id_no',
            'first_name' => 'mem_first_name',
            'last_name' => 'mem_last_name',
            'middle_name' => 'mem_middle_name',
        ],
        'nonmembers' => [
            'record_type' => 'nonmember',
            'id' => 'nm_id_no',
            'first_name' => 'nm_first_name',
            'last_name' => 'nm_last_name',
            'middle_name' => 'nm_middle_name',
        ],
    ];

    public function handle(): int
    {
        $records = [];

        foreach (self::SOURCES as $argument => $mapping) {
            $path = $this->argument($argument);
            $rows = $this->readCsv($path, $mapping);

            if ($rows === null) {
                return self::FAILURE;
            }

            $records = [...$records, ...$rows];
        }

        try {
            DB::transaction(function () use ($records): void {
                $vipRecords = DB::table('master_lists')->whereNotNull('vip_name')->orWhere('record_type', 'vip')->get();
                $vipById = [];
                foreach ($vipRecords as $vip) {
                    if ($vip->psa_id !== null) {
                        $vipById[mb_strtolower(trim($vip->psa_id))] = $vip->id;
                    }
                }

                DB::table('master_lists')->whereNull('vip_name')->where('record_type', '!=', 'vip')->delete();
                $newRecords = [];
                foreach ($records as $record) {
                    $key = mb_strtolower(trim($record['psa_id']));
                    if (isset($vipById[$key])) {
                        $update = $record;
                        unset($update['created_at']);
                        DB::table('master_lists')->where('id', $vipById[$key])->update($update);
                        unset($vipById[$key]);
                    } else {
                        $newRecords[] = $record;
                    }
                }

                foreach (array_chunk($newRecords, 200) as $chunk) {
                    DB::table('master_lists')->insert($chunk);
                }
            });
        } catch (Throwable $exception) {
            $this->error('Import failed; the previous master list was retained: ' . $exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Master list replaced: ' . count($records) . ' records imported.');

        return self::SUCCESS;
    }

    private function readCsv(string $path, array $mapping): ?array
    {
        $fullPath = is_file($path) ? $path : base_path($path);
        $handle = @fopen($fullPath, 'r');
        if ($handle === false) {
            $this->error("Unable to open CSV file: {$path}");

            return null;
        }

        try {
            $header = fgetcsv($handle);

            if ($header === false) {
                throw new RuntimeException('CSV is empty.');
            }

            $header = array_map(static function ($column): string {
                return trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $column));
            }, $header);

            $required = [$mapping['id'], $mapping['first_name'], $mapping['last_name'], $mapping['middle_name'], 'payment_ref_no'];
            $missing = array_diff($required, $header);

            if ($missing !== []) {
                throw new RuntimeException('Missing columns: ' . implode(', ', $missing));
            }

            $indexes = array_flip($header);
            $records = [];
            $rowNumber = 1;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $psaId = $this->csvValue($row[$indexes[$mapping['id']]] ?? null);

                if ($psaId === null) {
                    throw new RuntimeException("Row {$rowNumber} has an empty PSA ID.");
                }

                $firstName = $this->csvValue($row[$indexes[$mapping['first_name']]] ?? null);
                $lastName = $this->csvValue($row[$indexes[$mapping['last_name']]] ?? null);

                if ($firstName === null || $lastName === null) {
                    $this->warn("{$path}: row {$rowNumber} skipped because the first or last name is missing.");
                    continue;
                }

                $records[] = [
                    'psa_id' => $psaId,
                    'record_type' => $mapping['record_type'],
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'middle_name' => $this->csvValue($row[$indexes[$mapping['middle_name']]] ?? null),
                    'payment_ref_no' => $this->csvValue($row[$indexes['payment_ref_no']] ?? null),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            return $records;
        } catch (Throwable $exception) {
            $this->error("Invalid CSV {$path}: {$exception->getMessage()}");

            return null;
        } finally {
            fclose($handle);
        }
    }

    private function csvValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' || strtoupper($value) === 'NULL' ? null : $value;
    }
}
