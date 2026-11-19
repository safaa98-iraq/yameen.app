<?php

namespace App\Console\Commands;

use App\Support\PublicPages;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;

/**
 * Render every public page to static HTML, for a preview on static hosting
 * (GitHub Pages). Only browser-side features work there: accounts, credits
 * and payments need the real PHP server.
 */
class ExportStatic extends Command
{
    protected $signature = 'site:export
        {out : Output directory}
        {--base-url= : Public base URL, e.g. https://user.github.io/repo}';

    protected $description = 'Export public pages as static HTML for a preview deployment';

    public function handle(Kernel $kernel): int
    {
        $out = rtrim($this->argument('out'), '/');
        $base = rtrim($this->option('base-url') ?: config('app.url'), '/');

        // All generated URLs (links, assets, font files) point at the public base URL.
        config(['app.url' => $base, 'app.asset_url' => $base, 'yameen.fonts.public_url' => $base.'/font-files']);
        URL::forceRootUrl($base);
        if (str_starts_with($base, 'https://')) {
            URL::forceScheme('https');
        }

        File::deleteDirectory($out);
        File::ensureDirectoryExists($out);

        $paths = $this->paths();
        $failed = 0;
        foreach ($paths as $path) {
            $response = $kernel->handle(Request::create($path, 'GET'));
            if ($response->getStatusCode() !== 200) {
                $this->error("{$path}: HTTP {$response->getStatusCode()}");
                $failed++;

                continue;
            }
            $file = $out.($path === '/' ? '' : $path).'/index.html';
            File::ensureDirectoryExists(dirname($file));
            File::put($file, $response->getContent());
        }

        // Plain files served at the root.
        foreach (['/sitemap.xml', '/robots.txt'] as $path) {
            File::put($out.$path, $kernel->handle(Request::create($path, 'GET'))->getContent());
        }

        // 404 page for static hosts: reuse the home page shell.
        if (is_file($out.'/index.html')) {
            File::copy($out.'/index.html', $out.'/404.html');
        }

        $this->copyAssets($out);
        $this->info('Exported '.(count($paths) - $failed).' pages to '.$out);

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /** @return list<string> */
    private function paths(): array
    {
        $paths = [];
        foreach (array_keys(config('yameen.locales')) as $locale) {
            foreach (app(PublicPages::class)->paths() as $path) {
                $paths[] = PublicPages::localize($path, $locale);
            }
        }

        return $paths;
    }

    private function copyAssets(string $out): void
    {
        File::copyDirectory(public_path('build'), $out.'/build');
        foreach (['favicon.svg', 'og-default.png'] as $file) {
            if (is_file(public_path($file))) {
                File::copy(public_path($file), $out.'/'.$file);
            }
        }

        // Font files (full WOFF2 files are not needed by any page).
        $src = config('yameen.fonts.path').'/files';
        if (! is_dir($src)) {
            return;
        }
        // All built font files plus the manifest, so a server deployment (the
        // demo Docker image) can import this exact build instead of rebuilding.
        foreach (File::allFiles($src) as $file) {
            $dest = $out.'/font-files/'.$file->getRelativePath().'/'.$file->getFilename();
            File::ensureDirectoryExists(dirname($dest));
            File::copy($file->getPathname(), $dest);
        }
        File::copy(config('yameen.fonts.path').'/fonts.json', $out.'/font-files/fonts.json');
        File::put($out.'/.nojekyll', '');
    }
}
