<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class AnnualConventionEvaluationController extends Controller
{
    public function show(): View
    {
        return view('pages.CME.evaluation');
    }

    public function check(Request $request): View
    {
        $validated = $request->validate([
            'psa_id' => ['required', 'string', 'max:100'],
        ]);

        $psaId = trim($validated['psa_id']);
        $registration = Registration::query()
            ->whereRaw('LOWER(TRIM(psa_id)) = ?', [mb_strtolower($psaId)])
            ->where('status', '!=', Registration::STATUS_REJECTED)
            ->first();

        if (! $registration) {
            return view('pages.CME.evaluation', [
                'status' => 'not-registered',
                'psaId' => $psaId,
            ]);
        }

        $responsesUrl = config('convention.evaluation_responses_csv_url');
        $formUrl = config('convention.evaluation_form_url');

        if (! filled($responsesUrl) || ! filled($formUrl)) {
            return view('pages.CME.evaluation', [
                'status' => 'not-configured',
                'psaId' => $psaId,
            ]);
        }

        try {
            $response = Http::accept('text/csv')->timeout(10)->get($responsesUrl);

            if (! $response->successful()) {
                throw new \RuntimeException('Evaluation response sheet request failed.');
            }

            $alreadyCompleted = $this->responseSheetContains($response->body(), $registration->psa_id);
        } catch (Throwable $exception) {
            report($exception);

            return view('pages.CME.evaluation', [
                'status' => 'unavailable',
                'psaId' => $psaId,
            ]);
        }

        return view('pages.CME.evaluation', [
            'status' => $alreadyCompleted ? 'completed' : 'eligible',
            'psaId' => $psaId,
            'formUrl' => $formUrl,
        ]);
    }


    private function responseSheetContains(string $csv, string $psaId): bool
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $csv);
        rewind($stream);

        try {
            $headers = fgetcsv($stream);

            if ($headers === false) {
                throw new \RuntimeException('Evaluation response sheet is empty.');
            }

            $psaIdColumn = null;
            foreach ($headers as $index => $header) {
                $normalizedHeader = preg_replace('/[^a-z0-9]/', '', mb_strtolower((string) $header));

                if (in_array($normalizedHeader, ['psaid', 'psaidno', 'psaidnumber', 'psamemberid', 'psamemberidnumber'], true)) {
                    $psaIdColumn = $index;
                    break;
                }
            }

            if ($psaIdColumn === null) {
                throw new \RuntimeException('Evaluation response sheet has no PSA ID column.');
            }

            while (($row = fgetcsv($stream)) !== false) {
                if (isset($row[$psaIdColumn]) && mb_strtolower(trim($row[$psaIdColumn])) === mb_strtolower($psaId)) {
                    return true;
                }
            }

            return false;
        } finally {
            fclose($stream);
        }
    }
}
