<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\Finder\Finder;

// Generates large/ and thumbs/ WebP variants IN PLACE under public/images/gallery/...
// (no storage disk involved — matches GalleryImageProcessor's resize/quality settings:
//  thumb: scaleDown width 480, webp quality 70
//  large: scaleDown width 1920, webp quality 82
//
// After running this, gallery:restore (pointed at public_path('images/gallery'))
// will find matching pairs and populate the DB.
//
// Usage:
//   php artisan gallery:process-in-place --dry-run
//   php artisan gallery:process-in-place
//   php artisan gallery:process-in-place --event=aca_2025

class ProcessGalleryInPlace extends Command
{
    protected $signature = 'gallery:process-in-place
        {--event= : Only process this event folder (e.g. aca_2025). Processes all events if omitted.}
        {--dry-run : Preview what would be generated without writing any files}';

    protected $description = 'Generate large/thumbs WebP variants in place under public/images/gallery from existing originals';

    protected array $imageExtensions = ['jpg', 'jpeg', 'png'];

    protected ImageManager $manager;

    public function handle(): int
    {
        $this->manager = new ImageManager(new Driver());

        $dryRun = (bool) $this->option('dry-run');
        $galleryRoot = public_path('images/gallery');

        if (! is_dir($galleryRoot)) {
            $this->error("Gallery folder not found: {$galleryRoot}");
            return self::FAILURE;
        }

        $eventFolders = $this->option('event')
            ? array_filter([$galleryRoot . DIRECTORY_SEPARATOR . $this->option('event')], 'is_dir')
            : $this->subdirectories($galleryRoot);

        if (empty($eventFolders)) {
            $this->warn('No event folders found.');
            return self::SUCCESS;
        }

        $totalProcessed = 0;
        $totalSkipped = 0;
        $totalFailed = 0;

        foreach ($eventFolders as $eventPath) {
            $this->info("\nEvent: " . basename($eventPath));

            foreach ($this->subdirectories($eventPath) as $dayPath) {
                $this->info('  Day: ' . basename($dayPath));

                foreach ($this->subdirectories($dayPath) as $categoryPath) {
                    $originals = $this->imageFiles($categoryPath);

                    if (empty($originals)) {
                        continue;
                    }

                    $this->line('    Category: ' . basename($categoryPath) . ' — ' . count($originals) . ' image(s)');

                    $largeDir = $categoryPath . DIRECTORY_SEPARATOR . 'large';
                    $thumbsDir = $categoryPath . DIRECTORY_SEPARATOR . 'thumbs';

                    if (! $dryRun) {
                        if (! is_dir($largeDir)) {
                            mkdir($largeDir, 0755, true);
                        }
                        if (! is_dir($thumbsDir)) {
                            mkdir($thumbsDir, 0755, true);
                        }
                    }

                    foreach ($originals as $originalFullPath) {
                        $filename = basename($originalFullPath);
                        $name = pathinfo($filename, PATHINFO_FILENAME);

                        $thumbPath = $thumbsDir . DIRECTORY_SEPARATOR . "{$name}.webp";
                        $largePath = $largeDir . DIRECTORY_SEPARATOR . "{$name}.webp";

                        if (file_exists($thumbPath) && file_exists($largePath)) {
                            $totalSkipped++;
                            continue;
                        }

                        if ($dryRun) {
                            continue;
                        }

                        try {
                            $image = $this->manager->read($originalFullPath);

                            if (! file_exists($thumbPath)) {
                                $thumb = clone $image;
                                $thumb->scaleDown(width: 480);
                                file_put_contents($thumbPath, (string) $thumb->toWebp(70));
                            }

                            if (! file_exists($largePath)) {
                                $large = clone $image;
                                $large->scaleDown(width: 1920);
                                file_put_contents($largePath, (string) $large->toWebp(82));
                            }

                            $totalProcessed++;
                        } catch (\Throwable $e) {
                            $this->warn("      Failed on '{$filename}': " . $e->getMessage());
                            $totalFailed++;
                        }
                    }
                }
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('Dry run complete — nothing was written. Remove --dry-run to actually process.');
        } else {
            $this->info("Processed {$totalProcessed} image(s), skipped {$totalSkipped} (already had both variants), failed {$totalFailed}.");
        }

        return self::SUCCESS;
    }

    protected function subdirectories(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $finder = (new Finder())->directories()->in($path)->depth(0)->sortByName();

        return array_map(fn ($dir) => $dir->getPathname(), iterator_to_array($finder));
    }

    protected function imageFiles(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $finder = (new Finder())->files()->in($path)->depth(0)->sortByName();

        return array_values(array_filter(
            array_map(fn ($f) => $f->getPathname(), iterator_to_array($finder)),
            fn ($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $this->imageExtensions)
        ));
    }
}
