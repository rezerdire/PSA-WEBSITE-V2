<?php

namespace App\Services;

use App\Models\Member;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MemberIdCardService
{
    protected string $disk = 'members_qr';

    public function generate(Member $member): string
    {
        $manager = new ImageManager(new Driver());

        // 1. Build the QR as a raw PNG blob
        $qrCode = new QrCode($member->member_id_no);
        $qrCode->setSize(400);
        $qrCode->setMargin(0);
        $writer = new PngWriter();
        $qrPngData = $writer->write($qrCode)->getString();

        // 2. Load template + QR
        $canvas = $manager->read(public_path('psa_id_template.png'));
        $qrImage = $manager->read($qrPngData);

        // 3. Resize QR and paste onto the white box
$qrImage->resize(220, 220);
$canvas->place($qrImage, 'top-left', 209, 570);

        // 4. Overlay text — name, chapter, PSA ID no
$fontPath = 'C:/Windows/Fonts/arialbd.ttf';
            $canvas->text(strtoupper($member->full_name), 320, 450, function ($font) use ($fontPath) {
                $font->file($fontPath);
                $font->size(38);
                $font->color('#000000');
                $font->align('center');
                $font->valign('middle');
            });

            $canvas->text(strtoupper($member->chapter->name ?? ''), 320, 880, function ($font) use ($fontPath) {
                $font->file($fontPath);
                $font->size(20);
                $font->color('#000000');
                $font->align('center');
            });

            $canvas->text("PSA ID NO: {$member->member_id_no}", 320, 990, function ($font) use ($fontPath) {
                $font->file($fontPath);
                $font->size(20);
                $font->color('#ffffff');
                $font->align('center');
            });

        // 5. Save composed card
        $filename = "id-card-{$member->member_id_no}.png";
        Storage::disk($this->disk)->put($filename, (string) $canvas->encode());

        return $filename;
    }

    public function url(string $filename): string
    {
        return Storage::disk($this->disk)->url($filename);
    }
}