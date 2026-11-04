<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberQr;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MemberQrService
{
    protected string $disk = 'members_qr';
    protected string $cardDirectory = 'new QR';
    protected string $legacyCardDirectory = 'member_id';
    protected function fontPath(): string
    {
        $path = public_path('fonts/Roboto-Bold.ttf');

        if (!file_exists($path)) {
            throw new \RuntimeException("Font file missing: {$path}");
        }

        return $path;
    }

    protected function createCanvas(ImageManager $manager)
    {
        $blue = '#25408f';
        $canvas = $manager->create(638, 1013)->fill('#ffffff');

        $canvas->drawRectangle(0, 0, fn ($rectangle) => $rectangle
            ->size(638, 322)
            ->background($blue));
        $canvas->drawRectangle(0, 936, fn ($rectangle) => $rectangle
            ->size(638, 77)
            ->background($blue));

        $logoPath = public_path('Images/PSA_LOGO.png');

        if (is_file($logoPath)) {
            $logo = $manager->read($logoPath);
            $logo->resize(148, 148);
            $canvas->place($logo, 'top-left', 245, 20);
        }

        foreach ([
            ['PHILIPPINE SOCIETY OF', 190, 38],
            ['ANESTHESIOLOGISTS, INC.', 237, 38],
            ['psa-inc.org', 291, 38],
        ] as [$text, $y, $size]) {
            $canvas->text($text, 319, $y, function ($font) use ($size) {
                $font->file($this->fontPath());
                $font->size($size);
                $font->color('#ffffff');
                $font->align('center');
                $font->valign('middle');
            });
        }

        return $canvas;
    }

    protected function existingCardPath(Member $member): ?string
    {
        $existing = $member->qr;

        if ($existing && Storage::disk($this->disk)->exists($existing->qr_path)) {
            return $existing->qr_path;
        }

        $chapterCode = $member->psa_chapter_code ?: 'UNASSIGNED';
        $directory = "{$this->legacyCardDirectory}/{$chapterCode}";
        $prefix = "{$member->member_id_no}_";

        foreach (Storage::disk($this->disk)->files($directory) as $path) {
            $filename = basename($path);

            if (
                str_starts_with($filename, $prefix)
                && str_ends_with(strtolower($filename), '.png')
            ) {
                return $path;
            }
        }

        return null;
    }

    public function generate(Member $member, bool $force = false): MemberQr
    {
        $hash = hash('sha256', $member->member_id_no);
        $existing = $member->qr;

        if (!$force) {
            $existingPath = $this->existingCardPath($member);

            if ($existingPath) {
                if ($existing && $existing->qr_path === $existingPath) {
                    return $existing;
                }

                return MemberQr::updateOrCreate(
                    ['member_id_no' => $member->member_id_no],
                    ['qr_path' => $existingPath, 'qr_hash' => $hash]
                );
            }
        }

        // --- 1. Build QR code ---
        $qrCode = new QrCode(
            data: $member->member_id_no,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            size: 400,
            margin: 0,
        );

        $svgWriter = new SvgWriter();
        Storage::disk($this->disk)->put(
            "qr/{$member->member_id_no}.svg",
            $svgWriter->write($qrCode)->getString()
        );

        $pngWriter = new PngWriter();
        $qrPngData = $pngWriter->write($qrCode)->getString();

        $manager = new ImageManager(new Driver());
        $canvas = $this->createCanvas($manager);
        $qrImage = $manager->read($qrPngData);

        $width = $canvas->width();

        $qrSize = 232;
        $qrImage->resize($qrSize, $qrSize);
        $canvas->place($qrImage, 'top-left', intdiv($width - $qrSize, 2), 584);

        // --- 3. NAME: use the middle-name initial, then wrap and shrink-to-fit ---
        $firstName = mb_strtoupper(trim($member->mem_first_name));
        $middleName = trim((string) $member->mem_middle_name);
        $middleInitial = $middleName !== ''
            ? rtrim(mb_strtoupper(mb_substr($middleName, 0, 1)), '.') . '.'
            : '';
        $lastName = mb_strtoupper(trim($member->mem_last_name));
        $nameParts = array_filter([$firstName, $middleInitial, $lastName]);
        $fullName = implode(' ', $nameParts);

        if (!preg_match('/,\s*M\.?D\.?$/i', $fullName)) {
            $fullName .= ', MD';
        }

        $this->drawFittedMultiline(
            canvas: $canvas,
            text: $fullName,
            areaTop: 385,
            areaBottom: 500,
            areaWidth: $width - 40,
            maxFontSize: 59,
            minFontSize: 18,
            color: '#000000',
            canvasWidth: $width,
        );

        // --- 4. CHAPTER: shrink-to-fit, single line ---
        $chapter = strtoupper($member->chapter->psa_chapter_desc ?? '');
        $this->drawFittedSingleLine(
            canvas: $canvas,
            text: $chapter,
            y: 897,
            areaWidth: $width - 40,
            maxFontSize: 43,
            minFontSize: 18,
            color: '#000000',
            canvasWidth: $width,
        );

        // --- 5. PSA ID NO (fixed size, white, footer) ---
        $canvas->text("PSA ID NO: {$member->member_id_no}", (int) ($width / 2), 970, function ($font) {
            $font->file($this->fontPath());
            $font->size(29);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('middle');
        });

        $safeName = preg_replace('/[^A-Z0-9 .]/', '', implode(' ', $nameParts));

        $chapterCode = $member->psa_chapter_code ?: 'UNASSIGNED';

        $filename = "{$member->member_id_no}_{$safeName}.png";
        $cardPath = "{$this->cardDirectory}/{$chapterCode}/{$filename}";

        Storage::disk($this->disk)->put($cardPath, (string) $canvas->toPng());

        return MemberQr::updateOrCreate(
            ['member_id_no' => $member->member_id_no],
            ['qr_path' => $cardPath, 'qr_hash' => $hash]
        );
    }

    /**
     * Wrap and shrink text inside a bounded card area.
     */
    protected function drawFittedMultiline(
        $canvas,
        string $text,
        int $areaTop,
        int $areaBottom,
        int $areaWidth,
        int $maxFontSize,
        int $minFontSize,
        string $color,
        int $canvasWidth,
    ): void {
        $fontSize = $maxFontSize;
        $lines = [$text];
        $lineHeight = 0;
        $totalHeight = 0;

        while ($fontSize >= $minFontSize) {
            $lines = [];
            $currentLine = '';
            $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [''];

            foreach ($words as $word) {
                $candidate = $currentLine === '' ? $word : "{$currentLine} {$word}";
                $box = imagettfbbox($fontSize, 0, $this->fontPath(), $candidate);
                $candidateWidth = (int) round(abs($box[2] - $box[0]) * 0.76);

                if ($currentLine === '' || $candidateWidth <= $areaWidth) {
                    $currentLine = $candidate;
                    continue;
                }

                $lines[] = $currentLine;
                $currentLine = $word;
            }

            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }

            $lineHeight = max(1, (int) round($fontSize * 0.76 * 1.2));
            $totalHeight = $lineHeight * count($lines);
            $maxLineWidth = 0;

            foreach ($lines as $line) {
                $box = imagettfbbox($fontSize, 0, $this->fontPath(), $line);
                $maxLineWidth = max(
                    $maxLineWidth,
                    (int) round(abs($box[2] - $box[0]) * 0.76),
                );
            }

            if ($maxLineWidth <= $areaWidth && $totalHeight <= ($areaBottom - $areaTop)) {
                break;
            }

            $fontSize--;
        }

        $startY = $areaTop
            + (int) (($areaBottom - $areaTop - $totalHeight) / 2)
            + (int) ($lineHeight / 2);

        foreach ($lines as $index => $line) {
            $canvas->text(
                $line,
                (int) ($canvasWidth / 2),
                $startY + ($index * $lineHeight),
                function ($font) use ($fontSize, $color) {
                    $font->file($this->fontPath());
                    $font->size($fontSize);
                    $font->color($color);
                    $font->align('center');
                    $font->valign('middle');
                },
            );
        }
    }

    /**
     * Shrink-to-fit single-line text (no wrapping) for chapter names.
     */
    protected function drawFittedSingleLine(
        $canvas,
        string $text,
        int $y,
        int $areaWidth,
        int $maxFontSize,
        int $minFontSize,
        string $color,
        int $canvasWidth,
    ): void {
        $fontSize = $maxFontSize;

        while ($fontSize >= $minFontSize) {
            $box = imagettfbbox($fontSize, 0, $this->fontPath(), $text);
            $textWidth = (int) round(abs($box[2] - $box[0]) * 0.76);

            if ($textWidth <= $areaWidth) {
                break;
            }

            $fontSize -= 1;
        }

        $canvas->text($text, (int) ($canvasWidth / 2), $y, function ($font) use ($fontSize, $color) {
            $font->file($this->fontPath());
            $font->size($fontSize);
            $font->color($color);
            $font->align('center');
            $font->valign('middle');
        });
    }

    public function url(MemberQr $qr): string
    {
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $qr->qr_path)));
        return Storage::disk($this->disk)->url($encodedPath);
    }

    public function svgUrl(Member $member): string
    {
        return Storage::disk($this->disk)->url("qr/{$member->member_id_no}.svg");
    }
}