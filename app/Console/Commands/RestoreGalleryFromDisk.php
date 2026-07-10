<?php

namespace App\Console\Commands;

use App\Models\GalleryCategory;
use App\Models\GalleryDay;
use App\Models\GalleryEvent;
use App\Models\GalleryImage;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

// Rebuilds gallery_events / gallery_days / gallery_categories / gallery_images
// from files that are ALREADY on disk under storage/app/public/gallery/...
// Use this after something wiped the DB (e.g. migrate:fresh) but the files survived.
//
// Expected layout per category folder:
//   gallery/{event-slug}/{day-slug}/{category-slug}/original.jpg
//   gallery/{event-slug}/{day-slug}/{category-slug}/large/original.webp
//   gallery/{event-slug}/{day-slug}/{category-slug}/thumbs/original.webp
//
// large/ and thumbs/ files are matched to the original by basename (extension ignored).
//
// Usage:
//   php artisan gallery:restore --dry-run
//   php artisan gallery:restore
//   php artisan gallery:restore --event=aca-2025   (restore just one event folder)

class RestoreGalleryFromDisk extends Command
{
    protected $signature = 'gallery:restore
        {--event= : Only restore this event slug (folder name under storage/app/public/gallery). Restores all events if omitted.}
        {--dry-run : Preview what would be created without touching the DB}';

    protected $description = 'Rebuild gallery_* DB rows from existing processed files already on disk';

    protected array $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $galleryRoot = storage_path('app/public/gallery');

        if (! is_dir($galleryRoot)) {
            $this->error("Gallery folder not found: {$galleryRoot}");
            return self::FAILURE;
        }

        $eventFolders = $this->option('event')
            ? array_filter([$galleryRoot . DIRECTORY_SEPARATOR . $this->option('event')], 'is_dir')
            : $this->subdirectories($galleryRoot);

        if (empty($eventFolders)) {
            $this->warn('No event folders found to restore.');
            return self::SUCCESS;
        }

        $totalImages = 0;
        $totalSkipped = 0;

        foreach ($eventFolders as $eventPath) {
            $eventSlug = basename($eventPath);
            $eventName = Str::headline($eventSlug);

            $this->info("\nEvent: {$eventName} ({$eventSlug})");

            $event = $dryRun
                ? null
                : GalleryEvent::firstOrCreate(
                    ['slug' => $eventSlug],
                    ['name' => $eventName]
                );

            foreach ($this->subdirectories($eventPath) as $dayPath) {
                $daySlug = Str::slug(basename($dayPath));
                $dayLabel = Str::title(preg_replace('/day(\d+)/i', 'Day $1', basename($dayPath)));

                $this->info("  Day: {$dayLabel} ({$daySlug})");

                $day = $dryRun
                    ? null
                    : GalleryDay::firstOrCreate(
                        ['gallery_event_id' => $event->id, 'slug' => $daySlug],
                        ['label' => $dayLabel, 'order' => 0]
                    );

                foreach ($this->subdirectories($dayPath) as $categoryPath) {
                    $rawName = basename($categoryPath);
                    $categorySlug = Str::slug($rawName);
                    $categoryName = Str::upper(trim(str_replace(['_', '-'], ' ', $rawName)));

                    // Only files directly inside the category folder are "originals" —
                    // large/ and thumbs/ are subfolders and won't be picked up here.
                    $originals = $this->imageFiles($categoryPath);
                    $largeDir = $categoryPath . DIRECTORY_SEPARATOR . 'large';
                    $thumbsDir = $categoryPath . DIRECTORY_SEPARATOR . 'thumbs';

                    if (empty($originals)) {
                        $this->warn("    Skipping '{$categoryName}' — no original files found directly in folder.");
                        continue;
                    }

                    $this->line("    Category: {$categoryName} — " . count($originals) . ' image(s)');

                    if ($dryRun) {
                        continue;
                    }

                    $category = GalleryCategory::firstOrCreate(
                        ['gallery_day_id' => $day->id, 'slug' => $categorySlug],
                        ['name' => $categoryName, 'order' => 0]
                    );

                    foreach ($originals as $index => $originalFullPath) {
                        $filename = basename($originalFullPath);
                        $basename = pathinfo($filename, PATHINFO_FILENAME);

                        $largeFile = $this->findByBasename($largeDir, $basename);
                        $thumbFile = $this->findByBasename($thumbsDir, $basename);

                        if (! $largeFile || ! $thumbFile) {
                            $this->warn("      Missing large/thumb for '{$filename}' — skipping.");
                            $totalSkipped++;
                            continue;
                        }

                        // Relative paths as stored on the 'public' disk (matches your Blade's Storage::disk('public')->url(...) usage)
                        $relBase = "gallery/{$eventSlug}/{$daySlug}/{$categorySlug}";
                        $relOriginal = "{$relBase}/{$filename}";
                        $relLarge = "{$relBase}/large/" . basename($largeFile);
                        $relThumb = "{$relBase}/thumbs/" . basename($thumbFile);

                        $alreadyExists = GalleryImage::where('gallery_category_id', $category->id)
                            ->where('path', $relOriginal)
                            ->exists();

                        if ($alreadyExists) {
                            $totalSkipped++;
                            continue;
                        }

                        [$width, $height] = $this->getDimensions($largeFile);

                        GalleryImage::create([
                            'gallery_category_id' => $category->id,
                            'path' => $relOriginal,
                            'thumb_path' => $relThumb,
                            'large_path' => $relLarge,
                            'width' => $width,
                            'height' => $height,
                            'order' => $index,
                        ]);

                        $totalImages++;
                    }
                }
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('Dry run complete — nothing was written. Remove --dry-run to actually restore.');
        } else {
            $this->info("Restored {$totalImages} image row(s), skipped {$totalSkipped} (already existing or missing pairs).");
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

    /**
     * Find a file inside $dir whose filename (without extension) matches $basename.
     */
    protected function findByBasename(string $dir, string $basename): ?string
    {
        foreach ($this->imageFiles($dir) as $file) {
            if (pathinfo($file, PATHINFO_FILENAME) === $basename) {
                return $file;
            }
        }

        return null;
    }

    protected function getDimensions(string $absolutePath): array
    {
        $size = @getimagesize($absolutePath);

        return $size ? [$size[0], $size[1]] : [null, null];
    }
}
