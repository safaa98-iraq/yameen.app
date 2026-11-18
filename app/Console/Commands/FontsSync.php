<?php

namespace App\Console\Commands;

use App\Models\Font;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class FontsSync extends Command
{
    protected $signature = 'fonts:sync
        {--skip-build : Only import the existing fonts.json (e.g. after editing overrides.json)}
        {--only= : Comma-separated google/fonts folder names to rebuild}
        {--repo= : Path to an existing google/fonts checkout}
        {--no-update : Do not fetch google/fonts before building}';

    protected $description = 'Build Arabic font files from google/fonts and import them into the fonts table';

    public function handle(): int
    {
        $path = config('yameen.fonts.path');

        if (! $this->option('skip-build') && ! $this->build($path)) {
            return self::FAILURE;
        }

        $manifestPath = $path.'/fonts.json';
        if (! is_file($manifestPath)) {
            $this->error("No manifest at {$manifestPath}. Run without --skip-build first.");

            return self::FAILURE;
        }

        $count = $this->import(json_decode(File::get($manifestPath), true, flags: JSON_THROW_ON_ERROR));
        $this->linkPublicFiles($path.'/files');
        $this->info("Imported {$count} fonts.");

        return self::SUCCESS;
    }

    private function build(string $path): bool
    {
        $cmd = [config('yameen.fonts.python'), base_path('scripts/fonts/build.py'),
            '--out', $path, '--pairings', config('yameen.fonts.pairings')];
        foreach (['only', 'repo'] as $opt) {
            if ($this->option($opt)) {
                array_push($cmd, '--'.$opt, $this->option($opt));
            }
        }
        if ($this->option('no-update')) {
            $cmd[] = '--no-update';
        }

        $process = new Process($cmd, base_path(), timeout: null);
        $process->run(fn ($type, $line) => $this->output->write($line));

        if (! $process->isSuccessful()) {
            $this->error('Font build failed. Install dependencies with: pip install -r scripts/fonts/requirements.txt');

            return false;
        }

        return true;
    }

    /** Upsert families from the manifest, apply overrides and delete families that disappeared. */
    public function import(array $manifest): int
    {
        $overrides = is_file($file = config('yameen.fonts.overrides'))
            ? json_decode(File::get($file), true, flags: JSON_THROW_ON_ERROR)
            : [];
        $styles = $overrides['styles'] ?? [];
        $rank = array_flip(array_values(array_unique($overrides['popularity'] ?? [])));
        $samples = $overrides['sample_text'] ?? [];
        $hidden = array_flip($overrides['hidden'] ?? []);

        $seen = [];
        DB::transaction(function () use ($manifest, $styles, $rank, $samples, $hidden, &$seen) {
            foreach ($manifest['fonts'] ?? [] as $f) {
                if (isset($hidden[$f['slug']])) {
                    continue;
                }
                $isArabic = ($f['kind'] ?? 'arabic') === 'arabic';
                // Usable in the fixer only with (nearly) full presentation-form coverage.
                $presentation = $isArabic && ($f['presentation_forms'] ?? 0) >= 0.98;

                Font::updateOrCreate(['slug' => $f['slug']], [
                    'name' => $f['name'],
                    'kind' => $f['kind'] ?? 'arabic',
                    'style' => $styles[$f['slug']] ?? $f['style'],
                    'category' => $f['category'] ?? null,
                    'designer' => $f['designer'] ?: null,
                    'license' => $f['license'] ?? 'OFL',
                    'weights' => $f['weights'],
                    'files' => $f['files'],
                    'presentation_forms' => $presentation,
                    'diacritics' => (bool) ($f['diacritics'] ?? false),
                    'variable' => (bool) ($f['variable'] ?? false),
                    'has_license_file' => (bool) ($f['has_license_file'] ?? false),
                    'sample_text' => $samples[$f['slug']] ?? null,
                    'popularity' => isset($rank[$f['slug']]) ? $rank[$f['slug']] + 1 : 1000,
                    'google_url' => $f['google_url'] ?? null,
                    'date_added' => $f['date_added'] ?? null,
                ]);
                $seen[] = $f['slug'];
            }

            Font::whereNotIn('slug', $seen)->delete();
        });

        cache()->forget('fonts:all');

        return count($seen);
    }

    private function linkPublicFiles(string $target): void
    {
        $link = public_path(ltrim(config('yameen.fonts.public_url'), '/'));
        if (! str_starts_with(config('yameen.fonts.public_url'), '/') || file_exists($link) || is_link($link)) {
            return;
        }
        File::ensureDirectoryExists($target);
        symlink($target, $link);
        $this->line("Linked {$link} -> {$target}");
    }
}
