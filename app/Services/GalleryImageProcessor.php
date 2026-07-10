<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
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

    public function process(string $originalPath): array
    {
        $fullPath = Storage::disk('public')->path($originalPath);
        $image = $this->manager->read($fullPath);

        $width = $image->width();
        $height = $image->height();

        $dir = Str::beforeLast($originalPath, '/');
        $name = Str::beforeLast(Str::afterLast($originalPath, '/'), '.');

        $thumbPath = "{$dir}/thumbs/{$name}.webp";
        $largePath = "{$dir}/large/{$name}.webp";

        $thumb = clone $image;
        $thumb->scaleDown(width: 480);
        Storage::disk('public')->put($thumbPath, (string) $thumb->toWebp(70));

        $large = clone $image;
        $large->scaleDown(width: 1920);
        Storage::disk('public')->put($largePath, (string) $large->toWebp(82));

        return [
            'thumb' => $thumbPath,
            'large' => $largePath,
            'width' => $width,
            'height' => $height,
        ];
    }
}