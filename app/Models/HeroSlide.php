<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class HeroSlide extends Model
{
    protected $fillable = [
        'eyebrow',
        'title',
        'highlight',
        'description',
        'details',
        'image_path',
        'image_paths',
        'image_alt',
        'show_image',
        'primary_label',
        'primary_url',
        'secondary_label',
        'secondary_url',
        'sort_order',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $slide): void {
            $paths = is_array($slide->image_paths) ? $slide->image_paths : [];

            if ($paths === [] && filled($slide->image_path)) {
                $paths = [$slide->image_path];
            }

            $slide->image_path = $slide->show_image === false ? null : ($paths[0] ?? null);
        });
    }

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'image_paths' => 'array',
            'show_image' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function imageSources(): array
    {
        if ($this->show_image === false) {
            return [];
        }

        $paths = is_array($this->image_paths) ? $this->image_paths : [];

        if ($paths === [] && filled($this->image_path)) {
            $paths = [$this->image_path];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $path): string => trim((string) $path),
            $paths,
        )));
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    protected function imageUrls(): Attribute
    {
        return Attribute::get(function (): array {
            return array_map(
                fn (string $path): string => $this->resolveImageUrl($path),
                $this->imageSources(),
            );
        });
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->imageUrls[0] ?? asset('images/stethoscopebg.png'));
    }

    protected function primaryHref(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->resolveButtonUrl($this->primary_url));
    }

    protected function secondaryHref(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->resolveButtonUrl($this->secondary_url));
    }

    private function resolveButtonUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^(?:[a-z][a-z0-9+.-]*:|\\/|#)/i', $value)) {
            return $value;
        }

        if (Route::has($value)) {
            try {
                return route($value);
            } catch (\Throwable) {
                return $value;
            }
        }

        return $value;
    }

    private function resolveImageUrl(string $path): string
    {
        if (filter_var($path, FILTER_VALIDATE_URL) || str_starts_with($path, '/')) {
            return $path;
        }

        if (Storage::disk('uploads')->exists($path)) {
            return '/uploads/' . ltrim($path, '/');
        }

        return asset($path);
    }
}
