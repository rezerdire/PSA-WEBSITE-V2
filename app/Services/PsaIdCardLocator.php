<?php

namespace App\Services;

use App\Models\UnregisteredPsaRecipient;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PsaIdCardLocator
{
    /** @var array<string, string>|null */
    private static ?array $pathsByPsaId = null;

    /** @var array<string, string>|null */
    private static ?array $uploadedPathsByPsaId = null;

    public static function flush(): void
    {
        self::$pathsByPsaId = null;
        self::$uploadedPathsByPsaId = null;
    }

    public function pathFor(string $psaId): ?string
    {
        $uploadedPaths = self::$uploadedPathsByPsaId ??= UnregisteredPsaRecipient::query()
            ->whereNotNull('id_card_path')
            ->pluck('id_card_path', 'psa_id')
            ->all();

        if (isset($uploadedPaths[$psaId])) {
            return Storage::disk('psa_id_cards')->path($uploadedPaths[$psaId]);
        }

        $pathsByPsaId = self::$pathsByPsaId ??= $this->indexCards();

        return $pathsByPsaId[$psaId] ?? null;
    }

    public function urlFor(string $psaId): ?string
    {
        $path = $this->pathFor($psaId);

        return $path === null
            ? null
            : asset('remaining/psa_id_remaining/' . rawurlencode(basename($path)));
    }

    /** @return array<string, string> */
    private function indexCards(): array
    {
        $directory = public_path('remaining/psa_id_remaining');
        if (! File::isDirectory($directory)) {
            return [];
        }

        $paths = [];
        foreach (File::files($directory) as $file) {
            if (! in_array(strtolower($file->getExtension()), ['png', 'jpg', 'jpeg'], true)) {
                continue;
            }

            $filename = $file->getFilename();
            if (preg_match('/^(.+?)_.+$/u', $filename, $matches) === 1) {
                $paths[$matches[1]] = $file->getPathname();
            }
        }

        return $paths;
    }
}
