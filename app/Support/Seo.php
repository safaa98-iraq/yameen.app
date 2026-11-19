<?php

namespace App\Support;

/** Structured data (JSON-LD) and meta helpers for the layout. */
class Seo
{
    /**
     * BreadcrumbList with Home first and the current page last.
     *
     * @param  array<int, array{0: string, 1: string}>  $trail  [label, url] pairs after Home.
     */
    public static function breadcrumbs(array $trail): array
    {
        $items = array_merge([[__('ui.breadcrumb_home'), lroute('home')]], $trail);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_map(fn ($item, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item[0],
                'item' => $item[1],
            ], $items, array_keys($items))),
        ];
    }

    public static function webApplication(string $name, string $description, string $url): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => $name,
            'description' => $description,
            'url' => $url,
            'applicationCategory' => 'DesignApplication',
            'operatingSystem' => 'Any',
            'browserRequirements' => 'Requires JavaScript',
            'inLanguage' => app()->getLocale(),
            'isAccessibleForFree' => true,
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
        ];
    }

    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => __('ui.brand'),
            'url' => lroute('home'),
            'inLanguage' => app()->getLocale(),
        ];
    }

    /** Page title without the " | Brand" suffix, for breadcrumbs and OG. */
    public static function shortTitle(string $title): string
    {
        return trim(preg_replace('/\s*\|\s*[^|]+$/u', '', $title));
    }

    public static function defaultImage(): string
    {
        return asset('og-default.png');
    }

    /** Preview deployments (e.g. GitHub Pages) must not be indexed. */
    public static function noindex(): bool
    {
        return (bool) config('yameen.seo.noindex');
    }

    public static function json(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    }
}
