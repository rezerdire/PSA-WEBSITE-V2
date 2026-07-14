<?php

namespace App\Console\Commands;

use App\Models\GalleryCategory;
use App\Models\GalleryDay;
use App\Models\GalleryEvent;
use App\Models\GalleryImage;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\Finder\Finder;

// Scans public/images/gallery/{event}/{day}/{category}/ for originals that already
// have matching large/ and thumbs/ webp variants (e.g. after gallery:process-in-place),
// and (re)populates the gallery_* tables from what's on disk — no re-encoding.
//
// Usage:
//   php artisan gallery:restore --dry-run
//   php artisan gallery:restore
//   php artisan gallery:restore --event=aca_2025

class RestoreGalleryFromDisk extends Command
{
    protected $signature = 'gallery:restore
        {--event= : Only restore this event folder (e.g. aca_2025). Restores all events if omitted.}
        {--dry-run : Preview what would be restored without touching the DB}';

    protected $description = 'Rebuild gallery_* DB rows from existing originals + large/thumbs webp variants under public/images/gallery';

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

        $totalRestored = 0;
        $totalSkipped = 0;
        $totalMissingVariant = 0;

        foreach ($eventFolders as $eventPath) {
            $eventSlug = basename($eventPath);
            $this->info("\nEvent: {$eventSlug}");

            $event = $dryRun
                ? GalleryEvent::firstWhere('slug', $eventSlug)
                : GalleryEvent::firstOrCreate(
                    ['slug' => $eventSlug],
                    ['name' => Str::headline($eventSlug)]
                );

            foreach ($this->subdirectories($eventPath) as $dayPath) {
                $daySlug = Str::slug(basename($dayPath));
                $dayLabel = Str::title(preg_replace('/day(\d+)/i', 'Day $1', basename($dayPath)));

                $this->info("  Day: {$dayLabel} ({$daySlug})");

                $day = $dryRun ? null : GalleryDay::firstOrCreate(
                    ['gallery_event_id' => $event->id, 'slug' => $daySlug],
                    ['label' => $dayLabel, 'order' => 0]
                );

                foreach ($this->subdirectories($dayPath) as $categoryPath) {
                    $rawName = basename($categoryPath);
                    $categorySlug = Str::slug($rawName);
                    $categoryName = Str::upper(trim(str_replace(['_', '-'], ' ', $rawName)));

                    $originals = $this->imageFiles($categoryPath);

                    if (empty($originals)) {
                        continue;
                    }

                    $this->line("    Category: {$categoryName} — " . count($originals) . ' image(s)');

                    $category = $dryRun ? null : GalleryCategory::firstOrCreate(
                        ['gallery_day_id' => $day->id, 'slug' => $categorySlug],
                        ['name' => $categoryName, 'order' => 0]
                    );

                    $largeDir = $categoryPath . DIRECTORY_SEPARATOR . 'large';
                    $thumbsDir = $categoryPath . DIRECTORY_SEPARATOR . 'thumbs';

                    foreach ($originals as $index => $originalFullPath) {
                        $filename = basename($originalFullPath);
                        $name = pathinfo($filename, PATHINFO_FILENAME);

                        $thumbFullPath = $thumbsDir . DIRECTORY_SEPARATOR . "{$name}.webp";
                        $largeFullPath = $largeDir . DIRECTORY_SEPARATOR . "{$name}.webp";

                        if (! file_exists($thumbFullPath) || ! file_exists($largeFullPath)) {
                            $this->warn("      Missing variant(s) for '{$filename}' — run gallery:process-in-place first.");
                            $totalMissingVariant++;
                            continue;
                        }

                        // Path relative to public/, matching what the app stores in the DB
                        $originalRelative = 'images/gallery/' . Str::after($originalFullPath, $galleryRoot . DIRECTORY_SEPARATOR);
                        $originalRelative = str_replace(DIRECTORY_SEPARATOR, '/', $originalRelative);

                        $thumbRelative = str_replace(DIRECTORY_SEPARATOR, '/',
                            'images/gallery/' . Str::after($thumbFullPath, $galleryRoot . DIRECTORY_SEPARATOR));
                        $largeRelative = str_replace(DIRECTORY_SEPARATOR, '/',
                            'images/gallery/' . Str::after($largeFullPath, $galleryRoot . DIRECTORY_SEPARATOR));

                        if ($dryRun) {
                            continue;
                        }

                        $alreadyExists = GalleryImage::where('gallery_category_id', $category->id)
                            ->where('path', $originalRelative)
                            ->exists();

                        if ($alreadyExists) {
                            $totalSkipped++;
                            continue;
                        }

                        try {
                            [$width, $height] = getimagesize($originalFullPath);
                        } catch (\Throwable $e) {
                            $this->warn("      Could not read dimensions for '{$filename}': " . $e->getMessage());
                            $width = $height = null;
                        }

                        GalleryImage::create([
                            'gallery_category_id' => $category->id,
                            'path' => $originalRelative,
                            'thumb_path' => $thumbRelative,
                            'large_path' => $largeRelative,
                            'width' => $width,
                            'height' => $height,
                            'order' => $index,
                        ]);

                        $totalRestored++;
                    }
                }
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('Dry run complete — nothing was written. Remove --dry-run to actually restore.');
        } else {
            $this->info("Restored {$totalRestored} image(s), skipped {$totalSkipped} already in DB, {$totalMissingVariant} missing variant(s).");
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