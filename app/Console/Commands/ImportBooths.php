<?php

namespace App\Console\Commands;

use App\Models\Booth;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class ImportBooths extends Command
{
    protected $signature = 'booths:import
                            {file : Path to the .xlsx or .csv file}
                            {--dry-run : Validate and preview without saving}';

    protected $description = 'Import booths from XLSX/CSV (columns: Category, Booth code, Booth Location, Booth Name). Rows with the same category + name are merged into one booth.';

    private const REQUIRED = ['category', 'booth_code', 'booth_location', 'booth_name'];

    public function handle(): int
    {
        $path = $this->argument('file');

        if (! is_file($path) || ! is_readable($path)) {
            $this->error("File not found or unreadable: {$path}");

            return self::FAILURE;
        }

        $reader = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'xlsx' => new XlsxReader,
            'csv' => new CsvReader,
            default => null,
        };

        if (! $reader) {
            $this->error('Unsupported file type. Use .xlsx or .csv.');

            return self::FAILURE;
        }

        $reader->open($path);

        $header = null;
        $rows = [];
        $errors = [];
        $filled = [];
        $seen = [];
        $prev = null; // last valid row, used to fill merged (blank) cells
        $line = 0;

        // Only the first sheet is read.
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $line++;
                $cells = array_map(
                    fn ($v) => trim((string) $v),
                    $row->toArray()
                );

                // Header row: "Booth code" -> booth_code
                if ($header === null) {
                    $cells[0] = preg_replace('/^\xEF\xBB\xBF/', '', $cells[0] ?? '');
                    $header = array_map(
                        fn ($h) => strtolower(preg_replace('/\s+/', '_', $h)),
                        $cells
                    );

                    $missing = array_diff(self::REQUIRED, $header);
                    if ($missing) {
                        $reader->close();
                        $this->error('Missing required column(s): '.implode(', ', $missing));
                        $this->line('Found: '.implode(', ', $header));

                        return self::FAILURE;
                    }

                    continue;
                }

                // Skip fully blank lines
                if (count(array_filter($cells, fn ($v) => $v !== '')) === 0) {
                    continue;
                }

                // Pad short rows so array_combine doesn't fail
                $cells = array_pad($cells, count($header), '');
                $data = array_combine($header, array_slice($cells, 0, count($header)));

                $data['booth_code'] = preg_replace('/\s+/', ' ', $data['booth_code']);

                if ($data['booth_code'] === '') {
                    $errors[] = "Line {$line}: 'booth_code' is empty.";

                    continue;
                }

                // Merged cells in Excel leave every cell blank except the first.
                // Category: inherit from the row above.
                if ($data['category'] === '') {
                    if (! $prev) {
                        $errors[] = "Line {$line}: 'category' is empty.";

                        continue;
                    }
                    $data['category'] = $prev['category'];
                }

                $sameCategory = $prev && $prev['key'] === $this->norm($data['category']);

                // Location: inherit within the same category.
                if ($data['booth_location'] === '' && $sameCategory) {
                    $data['booth_location'] = $prev['location'];
                }

                // Name: inherit within the same category (merged name cells).
                if ($data['booth_name'] === '') {
                    if ($sameCategory) {
                        $data['booth_name'] = $prev['name'];
                        $filled[] = "Line {$line}: {$data['booth_code']} -> '{$prev['name']}' (merged cell)";
                    } else {
                        $errors[] = "Line {$line}: 'booth_name' is empty.";

                        continue;
                    }
                }

                // Each booth code may only appear once.
                $key = $this->norm($data['booth_code']);
                if (isset($seen[$key])) {
                    $errors[] = "Line {$line}: duplicate booth code '{$data['booth_code']}' (first seen on line {$seen[$key]}).";

                    continue;
                }
                $seen[$key] = $line;

                $prev = [
                    'key' => $this->norm($data['category']),
                    'category' => $data['category'],
                    'location' => $data['booth_location'],
                    'name' => $data['booth_name'],
                ];

                $rows[$line] = $data;
            }

            break;
        }

        $reader->close();

        if ($header === null) {
            $this->error('The file is empty.');

            return self::FAILURE;
        }

        if ($errors) {
            $this->warn(count($errors).' row(s) skipped:');
            foreach ($errors as $error) {
                $this->line("  - {$error}");
            }
        }

        if (empty($rows)) {
            $this->error('No valid rows to import.');

            return self::FAILURE;
        }

        // Group rows that share the same category + booth name.
        $groups = [];
        foreach ($rows as $row) {
            $key = $this->norm($row['category']).'|'.$this->norm($row['booth_name']);

            $groups[$key] ??= [
                'category' => $row['category'],
                'name' => $row['booth_name'],
                'codes' => [],
                'locations' => [],
            ];

            $groups[$key]['codes'][] = $row['booth_code'];

            if ($row['booth_location'] !== '') {
                $groups[$key]['locations'][] = $row['booth_location'];
            }
        }

        $booths = array_map(function ($g) {
            $codes = array_values(array_unique($g['codes']));
            natcasesort($codes);

            return [
                'category' => $g['category'],
                'name' => $g['name'],
                'booth_code' => implode(', ', $codes),
                'location' => implode(' / ', array_unique($g['locations'])),
                'count' => count($g['codes']),
            ];
        }, array_values($groups));

        if ($filled) {
            $this->info(count($filled).' blank name(s) filled from merged cells:');
            foreach ($filled as $f) {
                $this->line("  - {$f}");
            }
        }

        $mergedCount = count($rows) - count($booths);

        if ($this->option('dry-run')) {
            $this->info(count($rows).' row(s) -> '.count($booths)." booth(s) ({$mergedCount} merged). Dry run, nothing saved.");

            $this->table(
                ['Category', 'Code', 'Generated', 'Location', 'Name', 'Rows'],
                array_map(fn ($b) => [
                    $b['category'],
                    $b['booth_code'],
                    Booth::makeGeneratedCode($b['booth_code'], $b['name']),
                    $b['location'],
                    $b['name'],
                    $b['count'],
                ], $booths)
            );

            return self::SUCCESS;
        }

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($booths, &$created, &$updated) {
            foreach ($booths as $b) {
                // Identity = category + name, so re-imports update instead of duplicating.
                $booth = Booth::firstOrNew([
                    'booth_type' => $b['category'],
                    'booth_name' => $b['name'],
                ]);

                $booth->exists ? $updated++ : $created++;

                $booth->booth_code = $b['booth_code'];
                $booth->location = $b['location'] !== '' ? $b['location'] : null;

                // save() fires the `saving` hook, which builds booth_generated_code
                // (only when empty, so hand-edited codes survive) and the QR.
                $booth->save();
            }
        });

        $this->info("Done. Created: {$created}, updated: {$updated} ({$mergedCount} row(s) merged into existing booths).");

        return self::SUCCESS;
    }

    /**
     * Normalize for comparison: collapse whitespace, uppercase.
     */
    private function norm(string $value): string
    {
        return strtoupper(preg_replace('/\s+/', ' ', trim($value)));
    }
}