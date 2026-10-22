<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('player_id', 64);
            $table->string('server_id', 32)->nullable();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('total');
            $table->foreignId('promotion_id')->nullable()->constrained()->nullOnDelete();
            $table->string('promo_code', 32)->nullable(); // snapshot kode promo
            // Status pesanan dan status pembayaran dipisah.
            $table->string('status', 20)->default('pending');
            $table->string('payment_status', 20)->default('unpaid');
            $table->string('payment_method', 32);
            // Kunci idempotensi: mencegah pesanan ganda saat form dikirim dua kali.
            $table->uuid('idempotency_key')->nullable()->unique();
            $table->string('topup_reference', 64)->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            // Snapshot nama & harga saat transaksi
            $table->string('game_name');
            $table->string('product_name');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
