<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('cryptocurrency_id')->constrained()->cascadeOnDelete();
            $table->string('fiat_currency');
            $table->decimal('price_per_unit', 20, 2);
            $table->decimal('amount', 20, 8);
            $table->decimal('total_price', 20, 2);
            $table->string('trade_status');
            $table->timestamps();

            $table->index(['order_id', 'trade_status']);
            $table->index(['buyer_id', 'trade_status']);
            $table->index(['seller_id', 'trade_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
