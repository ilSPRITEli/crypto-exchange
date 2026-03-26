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
        Schema::create('fiat_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('trade_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->string('fiat_currency');
            $table->decimal('amount', 20, 2);
            $table->string('transaction_type');
            $table->string('status');
            $table->timestamps();

            $table->index(['trade_id', 'status']);
            $table->index(['sender_id', 'status']);
            $table->index(['receiver_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiat_transactions');
    }
};

