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
        Schema::create(
            'orders',
            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('cryptocurrency_id')->constrained()->cascadeOnDelete();
                $table->string('order_type');
                $table->string('fiat_currency');
                $table->decimal('price_per_unit', 20, 2);
                $table->decimal('amount', 20, 8);
                $table->decimal('remaining_amount', 20, 8);
                $table->string('status');
                $table->timestamps();

                $table->index(['cryptocurrency_id', 'status']);
                $table->index(['user_id', 'status']);
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
