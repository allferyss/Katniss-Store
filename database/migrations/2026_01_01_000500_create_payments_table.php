<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 64)->unique();
            $table->string('driver', 20);
            $table->string('method', 32);
            $table->unsignedBigInteger('amount');
            $table->string('status', 20)->default('pending');
            $table->timestamp('paid_at')->nullable();
            // Jangan simpan data kartu/kredensial. Hanya metadata non-sensitif.
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        // Mencatat event webhook yang sudah diproses (anti-duplikasi).
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 100)->unique();
            $table->string('provider', 30);
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('payments');
    }
};
