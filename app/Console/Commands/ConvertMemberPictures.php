<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConvertMemberPictures extends Command
{
    /**
     * php artisan members:convert-pictures
     *
     * Reads public/mem_pic.csv (columns: member_id_no, mem_pic)
     * where mem_pic is a SQL Server hex-encoded image (0xFFD8FF...),
     * decodes it, and writes each as public/member-pics/{member_id_no}.jpg
     *
     * Also writes public/member-pics/mapping.csv with (psa_id, mem_pic)
     * ready to import into the member_pictures table.
     */
    protected $signature = 'members:convert-pictures
        {csv=mem_pic.csv : CSV filename inside public/}
        {--folder=member-pics : destination folder inside public/}';

    protected $description = 'Convert SSMS hex-encoded member photo CSV export into JPG files in public/';

    public function handle(): int
    {
        $csvPath = public_path($this->argument('csv'));
        $folder  = trim($this->option('folder'), '/');
        $destDir = public_path($folder);

        if (! File::exists($csvPath)) {
            $this->error("CSV not found at: {$csvPath}");
            return self::FAILURE;
        }

        if (! File::isDirectory($destDir)) {
            File::makeDirectory($destDir, 0755, true);
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            $this->error('Could not open CSV file.');
            return self::FAILURE;
        }

        // Read header row
        $header = fgetcsv($handle);
        if ($header === false) {
            $this->error('CSV appears empty.');
            return self::FAILURE;
        }

        // Strip UTF-8 BOM (and stray whitespace) that SSMS/Excel exports often prepend to the first header cell
        $header = array_map(function ($col) {
            $col = preg_replace('/^\xEF\xBB\xBF/', '', $col);
            return trim($col);
        }, $header);

        $idIdx  = array_search('member_id_no', $header);
        $picIdx = array_search('mem_pic', $header);

        if ($idIdx === false || $picIdx === false) {
            $this->error('CSV must have "member_id_no" and "mem_pic" columns. Found: ' . implode(', ', $header));
            fclose($handle);
            return self::FAILURE;
        }

        $mappingPath = $destDir . DIRECTORY_SEPARATOR . 'mapping.csv';
        $mappingFile = fopen($mappingPath, 'w');
        fputcsv($mappingFile, ['psa_id', 'mem_pic']);

        $success = 0;
        $failed  = 0;
        $rowNum  = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            $memberId = trim($row[$idIdx] ?? '');
            $hexRaw   = trim($row[$picIdx] ?? '');

            if ($memberId === '' || $hexRaw === '') {
                $this->warn("Row {$rowNum}: missing member_id_no or mem_pic, skipping.");
                $failed++;
                continue;
            }

            // Strip 0x / 0X prefix
            $hex = preg_replace('/^0x/i', '', $hexRaw);

            // Sanity check: must be valid hex, even length
            if (! ctype_xdigit($hex) || strlen($hex) % 2 !== 0) {
                $this->warn("Row {$rowNum} (member {$memberId}): invalid hex data, skipping.");
                $failed++;
                continue;
            }

            $binary = hex2bin($hex);
            if ($binary === false) {
                $this->warn("Row {$rowNum} (member {$memberId}): hex2bin failed, skipping.");
                $failed++;
                continue;
            }

            // Sanitize filename (member_id_no is already your PK, but strip anything unsafe just in case)
            $safeId = preg_replace('/[^A-Za-z0-9_\-]/', '', $memberId);
            $relativePath = $folder . '/' . $safeId . '.jpg'; // path to store in DB
            $absolutePath = public_path($relativePath);

            if (File::put($absolutePath, $binary) === false) {
                $this->warn("Row {$rowNum} (member {$memberId}): failed to write file.");
                $failed++;
                continue;
            }

            fputcsv($mappingFile, [$memberId, $relativePath]);
            $success++;
        }

        fclose($handle);
        fclose($mappingFile);

        $this->info("Done. Converted: {$success}, Skipped: {$failed}");
        $this->info("Images saved in: {$destDir}");
        $this->info("Mapping CSV for member_pictures table: {$mappingPath}");

        return self::SUCCESS;
    }
}