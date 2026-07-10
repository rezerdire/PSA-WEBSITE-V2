<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryEvent extends Model
{
     protected $fillable = ['name', 'slug'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function days(): HasMany
    {
        return $this->hasMany(GalleryDay::class)->orderBy('order');
    }
}
