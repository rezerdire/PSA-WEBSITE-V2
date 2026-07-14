<?php

namespace App\Observers;

use App\Jobs\ProcessGalleryImage;
use App\Models\GalleryImage;

class GalleryImageObserver
{
    /**
     * Handle the GalleryImage "created" event.
     */
public function created(GalleryImage $galleryImage): void
{
    if (! $galleryImage->thumb_path || ! $galleryImage->large_path) {
        ProcessGalleryImage::dispatch($galleryImage);
    }
}
    /**
     * Handle the GalleryImage "updated" event.
     */
    public function updated(GalleryImage $galleryImage): void
    {
        //
    }

    /**
     * Handle the GalleryImage "deleted" event.
     */
    public function deleted(GalleryImage $galleryImage): void
    {
        //
    }

    /**
     * Handle the GalleryImage "restored" event.
     */
    public function restored(GalleryImage $galleryImage): void
    {
        //
    }

    /**
     * Handle the GalleryImage "force deleted" event.
     */
    public function forceDeleted(GalleryImage $galleryImage): void
    {
        //
    }
}