<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class GalleryDay extends Model
{
    protected $fillable = ['gallery_event_id', 'label', 'slug', 'order'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(GalleryEvent::class, 'gallery_event_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(GalleryCategory::class)->orderBy('order');
    }
}
