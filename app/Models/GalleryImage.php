<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryImage extends Model
{
    protected $fillable = [
        'gallery_category_id', 'path', 'thumb_path', 'large_path',
        'width', 'height', 'order',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'gallery_category_id');
    }

        public function getThumbUrlAttribute(): string
    {
        return asset($this->thumb_path);
    }

    public function getLargeUrlAttribute(): string
    {
        return asset($this->large_path);
    }
}   