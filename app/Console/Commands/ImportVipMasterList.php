<?php

namespace App\Console\Commands;

use App\Models\MasterList;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\XLSX\Reader;
use RuntimeException;
use Throwable;

class ImportVipMasterList extends Command
{
    protected $signature = 'masterlist:import-vip {file : Path to the consolidated VIP XLSX workbook}';

    protected $description = 'Import VIP details from the consolidated workbook Summary sheet';

    public function handle(): int
    {
        $path = $this->argument('file');
        if (! is_file($path) || ! is_readable($path)) {
            $this->error("Cannot read workbook: {$path}");
            return self::FAILURE;
        }

        $reader = new Reader();
        try {
            $reader->open($path);
            $rows = [];
            $found = false;
            foreach ($reader->getSheetIterator() as $sheet) {
                if ($sheet->getName() !== 'Summary') {
                    continue;
                }
                $found = true;
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = array_pad($row->toArray(), 5, null);
                    [$psaId, $name, $role, $membership, $reference] = array_map(
                        static fn ($value) => trim((string) ($value ?? '')),
                        array_slice($cells, 0, 5),
                    );
                    if ($name === '' && $role === '' && $psaId === '' && $reference === '') {
                        continue;
                    }
                    if ($name === '' || $role === '' || ! str_contains($name, ',')) {
                        throw new RuntimeException('The Summary sheet contains a row without a valid name or role.');
                    }
                    $rows[] = compact('psaId', 'name', 'role', 'membership', 'reference');
                }
                break;
            }
            if (! $found || $rows === []) {
                throw new RuntimeException('The workbook has no populated Summary sheet.');
            }
            $reader->close();

            $matched = 0;
            $added = 0;
            DB::transaction(function () use ($rows, &$matched, &$added): void {
                foreach ($rows as $row) {
                    $matches = MasterList::query()->where('vip_name', $row['name'])->get();
                    if ($matches->isEmpty() && $row['psaId'] !== '') {
                        $matches = MasterList::query()->where('psa_id', $row['psaId'])->get();
                    }
                    if ($matches->isEmpty() && $row['psaId'] === '' && $row['reference'] !== '') {
                        $matches = MasterList::query()->where('payment_ref_no', $row['reference'])->get();
                    }
                    if ($matches->count() > 1) {
                        throw new RuntimeException("Multiple master list records match VIP {$row['name']}; resolve the duplicate before importing.");
                    }
                    $record = $matches->first();
                    if ($record) {
                        $record->update(['vip_name' => $row['name'], 'vip_role' => $row['role']]);
                        $matched++;
                        continue;
                    }

                    [$lastName, $firstName] = array_map('trim', explode(',', $row['name'], 2));
                    MasterList::create([
                        'psa_id' => $row['psaId'] !== '' ? $row['psaId'] : null,
                        'record_type' => 'vip',
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'payment_ref_no' => $row['reference'] !== '' ? $row['reference'] : null,
                        'vip_name' => $row['name'],
                        'vip_role' => $row['role'],
                    ]);
                    $added++;
                }
            });
        } catch (Throwable $exception) {
            $this->error('VIP import failed; no changes were saved: ' . $exception->getMessage());
            return self::FAILURE;
        }

        $this->info("VIP import complete: {$matched} matched, {$added} added.");
        return self::SUCCESS;
    }
}
