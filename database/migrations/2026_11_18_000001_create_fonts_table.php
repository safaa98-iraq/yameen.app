<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fonts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('kind', 16)->default('arabic')->index(); // arabic | latin (pairing only)
            $table->string('style', 16)->index(); // kufi, naskh, sans, serif, display, handwriting
            $table->string('category', 32)->nullable(); // Google category, informational
            $table->string('designer')->nullable();
            $table->string('license', 16)->default('OFL');
            $table->json('weights');
            $table->json('files'); // {weight: {ttf, woff2, preview}}
            $table->boolean('presentation_forms')->default(false)->index(); // works in the fixer
            $table->boolean('diacritics')->default(false);
            $table->boolean('variable')->default(false);
            $table->boolean('has_license_file')->default(false);
            $table->string('sample_text')->nullable();
            $table->unsignedInteger('popularity')->default(1000)->index(); // lower = more popular
            $table->string('google_url')->nullable();
            $table->date('date_added')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fonts');
    }
};
