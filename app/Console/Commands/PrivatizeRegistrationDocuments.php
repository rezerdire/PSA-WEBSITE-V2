<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PrivatizeRegistrationDocuments extends Command
{
    protected $signature = 'registrations:privatize-documents {--delete-public : Remove public copies after every private copy is verified} {--force : Skip confirmation before deleting public copies}';

    protected $description = 'Copy registration attachments to private storage and optionally remove their public copies';

    private const DIRECTORIES = [
        'Registration/ProofofPayment',
        'Registration/ID-Upload',
        'registrations/payment-proofs',
        'registrations/discount-ids',
    ];

    public function handle(): int
    {
        $public = Storage::disk('uploads');
        $private = Storage::disk('local');
        $paths = [];

        foreach (self::DIRECTORIES as $directory) {
            foreach ($public->allFiles($directory) as $path) {
                if (!str_starts_with($path, $directory.'/') || str_contains($path, '..')) {
                    $this->error('Unsafe path found in registration attachment storage; no public files were removed.');
                    return self::FAILURE;
                }

                $paths[] = $path;
            }
        }

        foreach ($paths as $path) {
            try {
                if ($private->exists($path)) {
                    if (!hash_equals($this->hash($public, $path), $this->hash($private, $path))) {
                        $this->error('A private attachment path already exists with different contents; no public files were removed.');
                        return self::FAILURE;
                    }
                    continue;
                }

                $stream = $public->readStream($path);
                if (!is_resource($stream)) {
                    $this->error('A public attachment could not be read; no public files were removed.');
                    return self::FAILURE;
                }

                try {
                    $written = $private->writeStream($path, $stream);
                } finally {
                    fclose($stream);
                }

                if (!$written || !$private->exists($path)
                    || !hash_equals($this->hash($public, $path), $this->hash($private, $path))) {
                    $this->error('A private attachment copy failed verification; no public files were removed.');
                    return self::FAILURE;
                }

            } catch (Throwable) {
                $this->error('An attachment copy failed; no public files were removed.');
                return self::FAILURE;
            }
        }
        $verified = count($paths);


        if (!$this->option('delete-public')) {
            $this->info("Verified {$verified} private copies. Public copies remain; rerun with --delete-public after deployment is ready.");
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Delete the public copies now that every private copy has been verified?')) {
            $this->info("Verified {$verified} private copies. Public copies remain.");
            return self::SUCCESS;
        }

        foreach ($paths as $path) {
            try {
                if (!$private->exists($path)
                    || !hash_equals($this->hash($public, $path), $this->hash($private, $path))) {
                    $this->error('A private copy failed the final verification; no public files were removed.');
                    return self::FAILURE;
                }
            } catch (Throwable) {
                $this->error('A final attachment verification failed; no public files were removed.');
                return self::FAILURE;
            }
        }

        $deleted = 0;
        foreach ($paths as $path) {
            try {
                if (!$public->delete($path)) {
                    $this->error('A public copy could not be removed; run the command again after fixing storage permissions.');
                    return self::FAILURE;
                }
                $deleted++;
            } catch (Throwable) {
                $this->error('A public copy could not be removed; run the command again after fixing storage permissions.');
                return self::FAILURE;
            }

        }

        $privateCount = count($paths);
        $this->info("Private copies verified: {$privateCount}; public copies removed: {$deleted}.");
        return self::SUCCESS;
    }

    private function hash($disk, string $path): string
    {
        return hash('sha256', $disk->get($path));
    }
}
