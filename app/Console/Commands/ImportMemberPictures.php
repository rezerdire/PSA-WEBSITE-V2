<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportMemberPictures extends Command
{
    /**
     * php artisan members:import-pictures
     *
     * Reads public/member-pics/mapping.csv (columns: psa_id, mem_pic)
     * and upserts into the member_pictures table.
     */
    protected $signature = 'members:import-pictures
        {csv=member-pics/mapping.csv : CSV path relative to public/}
        {--chunk=200 : Rows per insert batch}';

    protected $description = 'Import psa_id/mem_pic mapping CSV into the member_pictures table';

    public function handle(): int
    {
        $csvPath = public_path($this->argument('csv'));

        if (! File::exists($csvPath)) {
            $this->error("CSV not found at: {$csvPath}");
            return self::FAILURE;
        }

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle);

        $header = array_map(fn ($c) => trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $c)), $header);

        $psaIdx = array_search('psa_id', $header);
        $picIdx = array_search('mem_pic', $header);

        if (($psaIdx === false || $picIdx === false) && count($header) === 2) {
            $psaIdx = 0;
            $picIdx = 1;
        }

        if ($psaIdx === false || $picIdx === false) {
            $this->error('CSV must have "psa_id" and "mem_pic" columns.');
            fclose($handle);
            return self::FAILURE;
        }

        $chunkSize = (int) $this->option('chunk');
        $buffer    = [];
        $total     = 0;
        $skipped   = 0;
        $rowNum    = 1;

        // Preload valid member IDs so we can skip orphan rows (no matching member)
        $validMemberIds = DB::table('members')->pluck('member_id_no')->flip();

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            $psaId  = trim($row[$psaIdx] ?? '');
            $memPic = trim($row[$picIdx] ?? '');

            if ($psaId === '' || $memPic === '') {
                $skipped++;
                continue;
            }

            if (! isset($validMemberIds[$psaId])) {
                $this->warn("Row {$rowNum}: psa_id '{$psaId}' has no matching member in members table, skipping.");
                $skipped++;
                continue;
            }

            $buffer[] = [
                'psa_id'     => $psaId,
                'mem_pic'    => $memPic,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($buffer) >= $chunkSize) {
                DB::table('member_pictures')->upsert(
                    $buffer,
                    ['psa_id'],                  // unique key to match on
                    ['mem_pic', 'updated_at']    // columns to update if it already exists
                );
                $total += count($buffer);
                $buffer = [];
            }
        }

        if (! empty($buffer)) {
            DB::table('member_pictures')->upsert(
                $buffer,
                ['psa_id'],
                ['mem_pic', 'updated_at']
            );
            $total += count($buffer);
        }

        fclose($handle);

        $this->info("Imported/updated: {$total}, Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
