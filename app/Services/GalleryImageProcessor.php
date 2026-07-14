<?php

namespace App\Services;

use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class GalleryImageProcessor
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * $originalPath is relative to public/, e.g. "gallery/aca-2025/day1/asean-night/IMG_001.jpg"
     */
    public function process(string $originalPath): array
    {
        $fullPath = public_path($originalPath);

        $image = $this->manager->read($fullPath);

        $width = $image->width();
        $height = $image->height();

        $dir = Str::beforeLast($originalPath, '/');
        $name = Str::beforeLast(Str::afterLast($originalPath, '/'), '.');

        $thumbRelative = "{$dir}/thumbs/{$name}.webp";
        $largeRelative = "{$dir}/large/{$name}.webp";

        $thumbFullPath = public_path($thumbRelative);
        $largeFullPath = public_path($largeRelative);

        if (! is_dir(dirname($thumbFullPath))) {
            mkdir(dirname($thumbFullPath), 0755, true);
        }
        if (! is_dir(dirname($largeFullPath))) {
            mkdir(dirname($largeFullPath), 0755, true);
        }

        $thumb = clone $image;
        $thumb->scaleDown(width: 480);
        file_put_contents($thumbFullPath, (string) $thumb->toWebp(70));

        $large = clone $image;
        $large->scaleDown(width: 1920);
        file_put_contents($largeFullPath, (string) $large->toWebp(82));

        return [
            'thumb' => $thumbRelative,
            'large' => $largeRelative,
            'width' => $width,
            'height' => $height,
        ];
    }
}