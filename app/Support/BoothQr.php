<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BoothQr
{
    public static function scanUrl(string $code): string
    {
        return Route::has('booth.scan')
            ? route('booth.scan', ['booth_code' => $code])
            : url('/booth/' . $code);
    }

    /** Creates public/booths-qr/{CODE}.svg and returns the relative path. */
    public static function generate(string $code): string
    {
        File::ensureDirectoryExists(public_path('booths-qr'));

        $relative = "booths-qr/{$code}.svg";

        File::put(
            public_path($relative),
            QrCode::format('svg')->size(600)->margin(2)->errorCorrection('M')->generate(self::scanUrl($code))
        );

        return $relative;
    }

    /** Public URL for display (ignores old values that stored a scan URL). */
    public static function url(?string $path): ?string
    {
        if (blank($path) || str_starts_with($path, 'http')) {
            return null;
        }

        return asset($path);
    }

    /** Deletes a stored QR file (only inside public/booths-qr). */
    public static function delete(?string $path): void
    {
        if (blank($path) || ! str_starts_with($path, 'booths-qr/')) {
            return;
        }

        File::delete(public_path($path));
    }
}