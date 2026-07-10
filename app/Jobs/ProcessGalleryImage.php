<?php

namespace App\Jobs;

use App\Models\GalleryImage;
use App\Services\GalleryImageProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessGalleryImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public GalleryImage $image) {}

    public function handle(GalleryImageProcessor $processor): void
    {
        $result = $processor->process($this->image->path);

        $this->image->update([
            'thumb_path' => $result['thumb'],
            'large_path' => $result['large'],
            'width' => $result['width'],
            'height' => $result['height'],
        ]);
    }
}