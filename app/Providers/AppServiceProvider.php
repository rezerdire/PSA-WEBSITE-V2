<?php

namespace App\Providers;

use App\Models\GalleryImage;
use App\Observers\GalleryImageObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        GalleryImage::observe(GalleryImageObserver::class);
    }
}