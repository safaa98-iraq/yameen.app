<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_codes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('code_hash'); // HMAC of the 6-digit code, never the code itself
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->json('value');
            $table->timestamps();
        });

        Schema::create('credit_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('ip_key', 64)->nullable(); // HMAC of the IP; raw IPs are never stored
            $table->string('cookie_key', 64)->nullable(); // HMAC of the visitor cookie ID
            $table->date('day'); // Asia/Baghdad calendar day
            $table->string('action', 16);
            $table->string('text_hash', 64)->nullable();
            $table->unsignedSmallInteger('cost');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'day']);
            $table->index(['ip_key', 'day']);
            $table->index(['cookie_key', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_usages');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('login_codes');
    }
};
