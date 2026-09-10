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
    protected string $fontPath = 'C:/Windows/Fonts/arialbd.ttf';

    public function generate(Member $member, bool $force = false): MemberQr
    {
        $hash = hash('sha256', $member->member_id_no);
        $existing = $member->qr;

        if (!$force && $existing && $existing->qr_hash === $hash
            && Storage::disk($this->disk)->exists($existing->qr_path)) {
            return $existing;
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

        // --- 2. Composite the ID card ---
        $manager = new ImageManager(new Driver());
        $canvas = $manager->read(public_path('psa_id_template.png'));
        $qrImage = $manager->read($qrPngData);

        $width = $canvas->width();

        $qrImage->resize(220, 220);
        $canvas->place($qrImage, 'top-left', 209, 570);

        // --- 3. NAME: shrink-to-fit + wrap, matching the Python logic ---
        $fullName = strtoupper($member->full_name);
        $this->drawFittedText(
            canvas: $canvas,
            text: $fullName,
            areaTop: 340,
            areaBottom: 540,
            areaWidth: $width - 60,
            maxFontSize: 55,
            minFontSize: 25,
            color: '#000000',
            canvasWidth: $width,
        );

        // --- 4. CHAPTER: shrink-to-fit, single line ---
        $chapter = strtoupper($member->chapter->psa_chapter_desc ?? '');
        $this->drawFittedSingleLine(
            canvas: $canvas,
            text: $chapter,
            y: 875,
            areaWidth: $width - 40,
            maxFontSize: 40,
            minFontSize: 18,
            color: '#000000',
            canvasWidth: $width,
        );

        // --- 5. PSA ID NO (fixed size, white, footer) ---
        $canvas->text("PSA ID NO: {$member->member_id_no}", (int) ($width / 2), 970, function ($font) {
            $font->file($this->fontPath);
            $font->size(22);
            $font->color('#ffffff');
            $font->align('center');
        });


        $firstName = strtoupper(trim($member->mem_first_name));
        $middleInitial = $member->mem_middle_name ? strtoupper(substr(trim($member->mem_middle_name), 0, 1)) . '.' : '';
        $lastName = strtoupper(trim($member->mem_last_name));

        $nameParts = array_filter([$firstName, $middleInitial, $lastName]);
        $safeName = preg_replace('/[^A-Z0-9 .]/', '', implode(' ', $nameParts));

        $chapterCode = $member->psa_chapter_code ?: 'UNASSIGNED';

        $filename = "{$member->member_id_no}_{$safeName}.png";
        $cardPath = "member_id/{$chapterCode}/{$filename}";

        Storage::disk($this->disk)->put($cardPath, (string) $canvas->encode());

        return MemberQr::updateOrCreate(
            ['member_id_no' => $member->member_id_no],
            ['qr_path' => $cardPath, 'qr_hash' => $hash]
        );
    }

    /**
     * Shrink-to-fit + word-wrap text inside a bounded box, vertically centered.
     * Mirrors the Python multiline shrink/wrap logic.
     */
    protected function drawFittedText(
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
            $words = explode(' ', $text);
            $lines = [];
            $current = '';

            foreach ($words as $word) {
                $test = $current !== '' ? "{$current} {$word}" : $word;
                $box = imagettfbbox($fontSize, 0, $this->fontPath, $test);
                $testWidth = abs($box[2] - $box[0]);

                if ($testWidth <= $areaWidth) {
                    $current = $test;
                } else {
                    if ($current !== '') {
                        $lines[] = $current;
                    }
                    $current = $word;
                }
            }
            if ($current !== '') {
                $lines[] = $current;
            }

            // measure total wrapped block height
            $lineHeight = (int) ($fontSize * 1.2);
            $totalHeight = $lineHeight * count($lines);

            // measure widest line
            $maxLineWidth = 0;
            foreach ($lines as $line) {
                $box = imagettfbbox($fontSize, 0, $this->fontPath, $line);
                $maxLineWidth = max($maxLineWidth, abs($box[2] - $box[0]));
            }

            if ($maxLineWidth <= $areaWidth && $totalHeight <= ($areaBottom - $areaTop)) {
                break;
            }

            $fontSize -= 2;
        }

        $startY = $areaTop + (int) (($areaBottom - $areaTop - $totalHeight) / 2) + (int) ($lineHeight / 2);

        foreach ($lines as $i => $line) {
            $y = $startY + ($i * $lineHeight);
            $canvas->text($line, (int) ($canvasWidth / 2), $y, function ($font) use ($fontSize, $color) {
                $font->file($this->fontPath);
                $font->size($fontSize);
                $font->color($color);
                $font->align('center');
                $font->valign('middle');
            });
        }
    }

    /**
     * Shrink-to-fit single-line text (no wrapping) — used for the chapter line.
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
            $box = imagettfbbox($fontSize, 0, $this->fontPath, $text);
            $textWidth = abs($box[2] - $box[0]);

            if ($textWidth <= $areaWidth) {
                break;
            }

            $fontSize -= 1;
        }

        $canvas->text($text, (int) ($canvasWidth / 2), $y, function ($font) use ($fontSize, $color) {
            $font->file($this->fontPath);
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