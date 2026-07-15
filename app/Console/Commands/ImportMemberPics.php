<?php

namespace App\Console\Commands;

use App\Models\Member;
use Illuminate\Console\Command;

class ImportMemberPics extends Command
{
    protected $signature = 'members:import-pics {csv : Path to the CSV file}';
    protected $description = 'Convert hex-encoded mem_pic blobs from CSV into image files and update members table';

  public function handle(): int
{
    $path = $this->argument('csv');

    if (!file_exists($path)) {
        $this->error("File not found: {$path}");
        return self::FAILURE;
    }

    $destDir = public_path('member-pics');
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $handle = fopen($path, 'r');
    $header = fgetcsv($handle);

    // Strip BOM and whitespace from header names
    $header = array_map(function ($col) {
        $col = preg_replace('/^\xEF\xBB\xBF/', '', $col); // remove UTF-8 BOM
        return trim($col);
    }, $header);

    $memberIdIdx = array_search('member_id_no', $header);
    $memPicIdx = array_search('mem_pic', $header);

    if ($memberIdIdx === false || $memPicIdx === false) {
        $this->error('CSV must contain member_id_no and mem_pic columns.');
        $this->line('Found headers: ' . implode(', ', $header));
        return self::FAILURE;
    }

    $count = 0;

    while (($row = fgetcsv($handle)) !== false) {
        $memberId = trim($row[$memberIdIdx]);
        $hex = trim($row[$memPicIdx]);

        if (empty($hex) || strtoupper($hex) === 'NULL') {
            continue;
        }

        $hex = preg_replace('/^0x/i', '', $hex);

        $binary = hex2bin($hex);

        if ($binary === false) {
            $this->warn("Skipping {$memberId}: invalid hex data");
            continue;
        }

        $filename = "{$memberId}.jpg";
        file_put_contents($destDir . DIRECTORY_SEPARATOR . $filename, $binary);

        Member::where('member_id_no', $memberId)->update([
            'mem_pic' => "member-pics/{$filename}",
        ]);

        $count++;
    }

    fclose($handle);

    $this->info("Imported {$count} member pictures.");
    return self::SUCCESS;
}
}