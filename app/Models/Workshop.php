<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    protected $fillable = [
        'code',
        'name',
        'session',
        'venue',
        'capacity',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public static function optionMap(bool $activeOnly = false): array
    {
        return static::query()
            ->when($activeOnly, fn (Builder $query): Builder => $query->active())
            ->ordered()
            ->pluck('name', 'code')
            ->all();
    }
}
