<?php

namespace App\Support\Content;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\FrontMatterParser;
use League\CommonMark\GithubFlavoredMarkdownConverter;

/**
 * Loads Markdown pages with YAML front matter (title, description, h1, intro, faq, ...).
 * The body is split into numbered sections at each "## " heading.
 */
class ContentRepository
{
    public function __construct(private readonly ?string $basePath = null) {}

    public function find(string $slug, ?string $locale = null): ?Page
    {
        $locale ??= App::getLocale();
        $path = $this->path($slug, $locale);

        if (! is_file($path)) {
            return null;
        }

        $key = 'content:'.$locale.':'.$slug.':'.filemtime($path);

        return Page::fromArray(Cache::rememberForever(
            $key,
            fn () => $this->parse($slug, $locale, file_get_contents($path))->toArray(),
        ));
    }

    public function get(string $slug, ?string $locale = null): Page
    {
        return $this->find($slug, $locale) ?? abort(404);
    }

    /**
     * Slugs of the pages in a content folder, e.g. slugs('blog') → ['photoshop-arabic', ...].
     * Uses the default locale; drafts (front matter "draft: true") are skipped.
     */
    public function slugs(string $folder, ?string $locale = null): array
    {
        $locale ??= config('yameen.default_locale');
        $dir = ($this->basePath ?? resource_path('content')).'/'.$locale.'/'.$folder;
        $slugs = [];
        foreach (glob($dir.'/*.md') ?: [] as $file) {
            $slug = $folder.'/'.basename($file, '.md');
            $page = $this->find($slug, $locale);
            if ($page && ! $page->meta('draft')) {
                $slugs[] = basename($file, '.md');
            }
        }
        sort($slugs);

        return $slugs;
    }

    public function path(string $slug, string $locale): string
    {
        return ($this->basePath ?? resource_path('content')).'/'.$locale.'/'.$slug.'.md';
    }

    public function parse(string $slug, string $locale, string $source): Page
    {
        $parsed = (new FrontMatterParser(new SymfonyYamlFrontMatterParser))->parse($source);
        $meta = (array) ($parsed->getFrontMatter() ?? []);
        $markdown = $this->markdown();

        $sections = [];
        $chunks = preg_split('/^##\s+(.+)$/m', $parsed->getContent(), -1, PREG_SPLIT_DELIM_CAPTURE);
        $lead = trim(array_shift($chunks));
        for ($i = 0; $i + 1 < count($chunks); $i += 2) {
            $sections[] = [
                'title' => trim($chunks[$i]),
                'html' => (string) $markdown->convert(trim($chunks[$i + 1])),
            ];
        }

        $faq = array_map(fn ($item) => [
            'q' => (string) ($item['q'] ?? ''),
            'a' => (string) ($item['a'] ?? ''),
        ], (array) ($meta['faq'] ?? []));

        $intro = $meta['intro'] ?? ($lead !== '' ? $lead : null);

        return new Page(
            slug: $slug,
            locale: $locale,
            title: (string) ($meta['title'] ?? ''),
            description: (string) ($meta['description'] ?? ''),
            h1: (string) ($meta['h1'] ?? $meta['title'] ?? ''),
            intro: $intro,
            sections: $sections,
            faq: $faq,
            meta: array_diff_key($meta, array_flip(['title', 'description', 'h1', 'intro', 'faq'])),
        );
    }

    private function markdown(): GithubFlavoredMarkdownConverter
    {
        return new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
