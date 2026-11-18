<?php

namespace App\Support;

use App\Models\Font;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Read access to the fonts table, cached as raw rows (Laravel does not
 * unserialize objects from the cache by default) and hydrated per request.
 */
class FontCatalog
{
    private ?Collection $all = null;

    /** @return Collection<int, Font> All families, Arabic and Latin, ranked. */
    public function all(): Collection
    {
        return $this->all ??= Font::hydrate(
            Cache::rememberForever('fonts:all', fn () => Font::query()->ranked()->toBase()->get()
                ->map(fn ($row) => (array) $row)->all())
        );
    }

    /** @return Collection<int, Font> */
    public function arabic(): Collection
    {
        return $this->all()->where('kind', 'arabic')->values();
    }

    /** Fonts with presentation forms: the ones that work in the fixer. */
    public function forApps(): Collection
    {
        return $this->arabic()->where('presentation_forms', true)->values();
    }

    public function find(?string $slug): ?Font
    {
        return $slug ? $this->arabic()->firstWhere('slug', $slug) : null;
    }

    public function latin(string $name): ?Font
    {
        return $this->all()->first(fn (Font $f) => $f->name === $name);
    }

    public function style(string $style): Collection
    {
        return $this->arabic()->where('style', $style)->values();
    }

    public function related(Font $font, int $limit = 3): Collection
    {
        $same = $this->style($font->style)->reject(fn (Font $f) => $f->is($font));

        return $same->concat($this->arabic()->reject(fn (Font $f) => $f->is($font) || $f->style === $font->style))
            ->take($limit)->values();
    }

    /** Default font of the fixer: IBM Plex Sans Arabic if present, else the top-ranked app font. */
    public function defaultForApps(): ?Font
    {
        return $this->forApps()->firstWhere('slug', 'ibm-plex-sans-arabic') ?? $this->forApps()->first();
    }

    public function pairings(): Collection
    {
        $file = config('yameen.fonts.pairings');
        $pairs = is_file($file) ? json_decode(file_get_contents($file), true) : [];

        return collect($pairs)->map(fn ($p) => [
            'arabic' => $this->find($p['arabic']),
            'latin' => $this->latin($p['latin']),
            'latin_name' => $p['latin'],
            'use' => $p['use'] ?? 'ui',
        ])->filter(fn ($p) => $p['arabic'] !== null)->values();
    }
}
