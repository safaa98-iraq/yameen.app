<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<\App\Models\Font> */
class FontFactory extends Factory
{
    public function definition(): array
    {
        $name = Str::title($this->faker->unique()->words(2, true)).' Arabic';
        $slug = Str::slug($name);

        return [
            'slug' => $slug,
            'name' => $name,
            'kind' => 'arabic',
            'style' => 'sans',
            'category' => 'SANS_SERIF',
            'designer' => 'Test Designer',
            'license' => 'OFL',
            'weights' => [400, 700],
            'files' => [
                '400' => ['ttf' => "$slug-400.a.ttf", 'woff2' => "$slug-400.a.woff2", 'preview' => "$slug-400-arabic.a.woff2"],
                '700' => ['ttf' => "$slug-700.a.ttf", 'woff2' => "$slug-700.a.woff2", 'preview' => "$slug-700-arabic.a.woff2"],
            ],
            'presentation_forms' => true,
            'diacritics' => true,
            'variable' => false,
            'has_license_file' => true,
            'popularity' => $this->faker->numberBetween(1, 100),
            'google_url' => 'https://fonts.google.com/specimen/'.str_replace(' ', '+', $name),
        ];
    }
}
