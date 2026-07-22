<?php

namespace App\Http\Controllers\Admin;

use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class RegistrationPdfController extends Controller
{
    public function export(Request $request)
    {
        // Support both ?from=&to= (range) and ?ids=1,2,3 (bulk action)
        if ($request->filled('ids')) {
            $ids = collect(explode(',', $request->query('ids')))
                ->map(fn ($v) => (int) trim($v))
                ->filter()
                ->unique()
                ->sort()
                ->values();

            abort_if($ids->isEmpty(), 422, 'No valid IDs provided.');

            $registrations = Registration::with('member')
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->get();

            $from = $ids->first();
            $to   = $ids->last();
        } else {
            $from = max(1, (int) $request->query('from', 1));
            $to   = (int) $request->query('to', 999999);

            abort_if($from > $to, 422, 'Reference "From" must be less than or equal to "To".');

            $registrations = Registration::with('member')
                ->whereBetween('id', [$from, $to])
                ->orderBy('id')
                ->get();
        }

        abort_if($registrations->isEmpty(), 404, 'No registrations found in that reference range.');

        // Encode proof_payment images as base64 so dompdf can render them
        $registrations->each(function (Registration $reg) {
            $reg->proof_payment_base64  = $this->encodeImage($reg->proof_payment);
        });

        $pdf = Pdf::loadView('pdf.registration-export', [
            'registrations' => $registrations,
            'from'          => $from,
            'to'            => $to,
            'generatedAt'   => now()->format('F d, Y  g:i A'),
        ])->setPaper('a4', 'portrait');

        $filename = 'PSA-Registrations-' . str_pad($from, 6, '0', STR_PAD_LEFT)
                  . '-to-' . str_pad($to, 6, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
    }

    private function encodeImage(?string $relativePath): ?string
{
    if (! $relativePath) {
        return null;
    }

    $disk = Storage::disk('uploads');

    if (! $disk->exists($relativePath)) {
        return null;
    }

    $mime = $disk->mimeType($relativePath) ?: 'image/jpeg';
    $data = base64_encode($disk->get($relativePath));

    return "data:{$mime};base64,{$data}";
}
}