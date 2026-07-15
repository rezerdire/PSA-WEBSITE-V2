<?php

use App\Models\Member;
use App\Models\Chapter;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ?string $scannedId    = null;
    public bool    $memberFound  = false;
    public bool    $notFound     = false;

    public string  $psaId        = '';
    public string  $firstName    = '';
    public string  $lastName     = '';
    public string  $middleName   = '';
    public string  $chapterName  = '';
    public string  $memberType   = '';
    public string  $email = '';
    public ?string $memPic = null;
    public string  $phonenumber = '';

    public $newPic = null;
    public bool $uploadingPic = false;

    public function lookup(string $code): void
    {
        $code = trim($code);
        $this->scannedId   = $code;
        $this->memberFound = false;
        $this->notFound    = false;
        $this->newPic      = null;

        if (!preg_match('/^\d{4}$/', $code)) {
            $this->notFound = true;
            return;
        }

        $member = Member::find($code);

        if (!$member) {
            $this->notFound = true;
            return;
        }

        $chapter = Chapter::find($member->psa_chapter_code);

        $this->psaId       = $code;
        $this->firstName   = $member->mem_first_name  ?? '';
        $this->lastName    = $member->mem_last_name   ?? '';
        $this->middleName  = $member->mem_middle_name ?? '';
        $this->chapterName = $chapter->psa_chapter_desc ?? ($member->psa_chapter_code ?? '');
        $this->memberType  = $member->membershipType?->Memtype ?? '';
        $this->memberFound = true;
        $this->phonenumber = $this->formatMobile($member->mem_mobile_no1 ?? '');
        $this->email       = $member->mem_email_address ?? '';
        $this->memPic      = $member->mem_pic ?? null;
    }

    // Livewire auto-calls this when $newPic is set by wire:model upload
    public function updatedNewPic(): void
    {
        $this->validate([
            'newPic' => 'required|image|max:5120',
        ]);

        $this->uploadingPic = true;

        $member = Member::find($this->psaId);

        if (!$member) {
            $this->uploadingPic = false;
            return;
        }

        $filename = "{$this->psaId}.jpg";
        $destDir  = public_path('member-pics');

        // Guard against a stray file existing where the directory should be
        if (is_file($destDir)) {
            unlink($destDir);
        }

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $sourcePath = $this->newPic->getRealPath();
        [$origWidth, $origHeight, $type] = getimagesize($sourcePath);

        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG  => imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            default        => imagecreatefromjpeg($sourcePath),
        };

        // Fix EXIF orientation for JPEGs from phone cameras
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($sourcePath);
            if (!empty($exif['Orientation'])) {
                $source = match ($exif['Orientation']) {
                    3 => imagerotate($source, 180, 0),
                    6 => imagerotate($source, -90, 0),
                    8 => imagerotate($source, 90, 0),
                    default => $source,
                };
            }
        }

        // Crop to square (center crop) then resize to 600x600
        $size = min($origWidth, $origHeight);
        $srcX = (int) (($origWidth - $size) / 2);
        $srcY = (int) (($origHeight - $size) / 2);

        $target = imagecreatetruecolor(600, 600);
        imagecopyresampled($target, $source, 0, 0, $srcX, $srcY, 600, 600, $size, $size);

        imagejpeg($target, $destDir . DIRECTORY_SEPARATOR . $filename, 85);

        imagedestroy($source);
        imagedestroy($target);

        $relativePath = "member-pics/{$filename}";

        $member->update(['mem_pic' => $relativePath]);

        $this->memPic = $relativePath . '?v=' . time();
        $this->newPic = null;
        $this->uploadingPic = false;
    }

    private function formatMobile(?string $number): string
    {
        $number = trim((string) $number);

        if ($number === '') {
            return '';
        }

        if (str_starts_with($number, '0')) {
            return $number;
        }

        if (str_starts_with($number, '63')) {
            return '0' . substr($number, 2);
        }

        if (preg_match('/^9\d{9}$/', $number)) {
            return '0' . $number;
        }

        return $number;
    }

    public function scanAgain(): void
    {
        $this->reset(['scannedId', 'memberFound', 'notFound', 'psaId', 'firstName', 'lastName', 'middleName', 'chapterName', 'memberType', 'phonenumber', 'email', 'memPic', 'newPic']);
        $this->dispatch('scanner-reset');
    }
};
?>