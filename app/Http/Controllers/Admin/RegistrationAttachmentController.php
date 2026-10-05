<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationAttachmentController extends Controller
{
    public function __invoke(Registration $registration, string $attachment): StreamedResponse
    {
        [$path, $prefixes] = match ($attachment) {
            'proof-payment' => [
                $registration->proof_payment,
                ['Registration/ProofofPayment/', 'registrations/payment-proofs/'],
            ],
            'discount-id' => [
                $registration->discount_id,
                ['Registration/ID-Upload/', 'registrations/discount-ids/'],
            ],
            default => [null, []],
        };

        abort_unless(
            is_string($path)
                && !str_contains($path, '..')
                && collect($prefixes)->contains(fn (string $prefix): bool => str_starts_with($path, $prefix)),
            404,
        );

        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, basename($path), [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ], 'inline');
    }
}
