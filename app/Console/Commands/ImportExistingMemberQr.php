<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\MemberQr;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportExistingMemberQr extends Command
{
    protected $signature = 'members:import-qr';
    protected $description = 'Backfill members_qr table from existing ID card image files';

    public function handle(): int
    {
        $disk = 'members_qr';
        $baseDir = 'member_id';

        $chapterFolders = Storage::disk($disk)->directories($baseDir);

        if (empty($chapterFolders)) {
            $this->error("No chapter folders found under {$baseDir}/ on disk '{$disk}'.");
            return self::FAILURE;
        }

        $imported = 0;
        $skipped = 0;
        $notFound = 0;

        foreach ($chapterFolders as $folder) {
            $files = Storage::disk($disk)->files($folder);

            foreach ($files as $filePath) {
                $filename = basename($filePath);

                if (!preg_match('/^(\d+)_/', $filename, $matches)) {
                    $this->warn("Skipped (no ID prefix): {$filename}");
                    $skipped++;
                    continue;
                }

                $memberId = $matches[1];

                $member = Member::find($memberId);

                if (!$member) {
                    $this->warn("No matching member for ID {$memberId} ({$filename})");
                    $notFound++;
                    continue;
                }

                $hash = hash('sha256', $member->member_id_no);

                MemberQr::updateOrCreate(
                    ['member_id_no' => $member->member_id_no],
                    ['qr_path' => $filePath, 'qr_hash' => $hash]
                );

                $imported++;
            }
        }

        $this->info("Done. Imported: {$imported}, Skipped: {$skipped}, Not found: {$notFound}");
        return self::SUCCESS;
    }
}