<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryCategory extends Model
{
    protected $fillable = ['gallery_day_id', 'name', 'slug', 'order'];

    public function day(): BelongsTo
    {
        return $this->belongsTo(GalleryDay::class, 'gallery_day_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class)->orderBy('order');
    }       
}