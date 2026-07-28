<?php

namespace App\Console\Commands;

use App\Models\Member;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportMembers extends Command
{
    protected $signature = 'members:import {csv : Path to the CSV file}';
    protected $description = 'Upsert members from an updated CSV export into the members table';

    protected array $expectedColumns = [
        'member_id_no',
        'psa_chapter_code',
        'psa_mem_type',
        'mem_stat',
        'mem_last_name',
        'mem_first_name',
        'mem_middle_name',
        'mem_home_address',
        'mem_mobile_no1',
        'mem_email_address',
        'mem_gender',
        'mem_prc_no',
    ];

    public function handle(): int
    {
        $path = $this->argument('csv');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        $header = array_map(function ($col) {
            $col = preg_replace('/^\xEF\xBB\xBF/', '', $col);
            return trim($col);
        }, $header);

        $missing = array_diff($this->expectedColumns, $header);
        if (!empty($missing)) {
            $this->error('CSV is missing expected columns: ' . implode(', ', $missing));
            $this->line('Found headers: ' . implode(', ', $header));
            return self::FAILURE;
        }

        $columnIndex = [];
        foreach ($this->expectedColumns as $col) {
            $columnIndex[$col] = array_search($col, $header);
        }

        $updated = 0;
        $inserted = 0;
        $skipped = 0;
        $rowNum = 1;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;

                $memberId = trim($row[$columnIndex['member_id_no']] ?? '');

                if (empty($memberId)) {
                    $this->warn("Row {$rowNum}: skipped, empty member_id_no");
                    $skipped++;
                    continue;
                }

                $data = [];
                foreach ($this->expectedColumns as $col) {
                    $value = trim($row[$columnIndex[$col]] ?? '');
                    $data[$col] = ($value === '' || strtoupper($value) === 'NULL') ? null : $value;
                }

                $exists = Member::where('member_id_no', $memberId)->exists();

                Member::updateOrCreate(
                    ['member_id_no' => $memberId],
                    $data
                );

                $exists ? $updated++ : $inserted++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            $this->error("Import failed and was rolled back: " . $e->getMessage());
            return self::FAILURE;
        }

        fclose($handle);

        $this->info("Import complete: {$inserted} inserted, {$updated} updated, {$skipped} skipped.");
        return self::SUCCESS;
    }
}