<?php

namespace App\Support\Content;

/** A content page parsed from resources/content/{locale}/{slug}.md. */
final class Page
{
    /**
     * @param  array<int, array{title: string, html: string}>  $sections  Body split at "## " headings.
     * @param  array<int, array{q: string, a: string}>  $faq
     * @param  array<string, mixed>  $meta  Remaining front matter keys.
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $locale,
        public readonly string $title,
        public readonly string $description,
        public readonly string $h1,
        public readonly ?string $intro,
        public readonly array $sections,
        public readonly array $faq,
        public readonly array $meta,
    ) {}

    /** Plain array form, safe for the cache (Laravel does not unserialize objects by default). */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }

    public function meta(string $key, mixed $default = null): mixed
    {
        return $this->meta[$key] ?? $default;
    }
}
