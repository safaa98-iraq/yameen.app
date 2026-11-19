<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->ulid('id')->primary(); // also the Wayl referenceId
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan', 16); // monthly | yearly
            $table->unsignedSmallInteger('days');
            $table->decimal('price_usd', 8, 2);
            $table->decimal('rate_used', 10, 2);
            $table->unsignedInteger('amount_iqd');
            $table->string('status', 16)->default('pending')->index(); // pending | paid | failed | refunded
            $table->string('wayl_code')->nullable();
            $table->text('checkout_url')->nullable();
            $table->unsignedInteger('refunded_iqd')->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('wayl_events', function (Blueprint $table) {
            $table->id();
            $table->string('source', 16)->default('webhook'); // webhook | reconcile
            $table->string('reference_id')->nullable()->index();
            $table->longText('payload');
            $table->timestamp('processed_at')->nullable();
            $table->string('result')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('amount_iqd');
            $table->text('reason');
            $table->unsignedSmallInteger('days_removed');
            $table->json('response')->nullable();
            $table->timestamps();
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 8); // font | text
            $table->text('value');
            $table->string('value_hash', 64);
            $table->timestamps();
            $table->unique(['user_id', 'type', 'value_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('wayl_events');
        Schema::dropIfExists('orders');
    }
};
