<?php

namespace Database\Seeders;

use App\Models\FiatTransaction;
use App\Models\Trade;
use Illuminate\Database\Seeder;

class FiatTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trades = Trade::query()->get();

        if ($trades->isEmpty()) {
            return;
        }

        $fiatCurrencies = ['THB', 'USD'];
        $types = ['payment', 'refund'];
        $statuses = ['pending', 'completed', 'failed'];

        foreach ($trades as $trade) {
            FiatTransaction::query()->create([
                'trade_id' => $trade->id,
                'sender_id' => $trade->buyer_id,
                'receiver_id' => $trade->seller_id,
                'fiat_currency' => fake()->randomElement($fiatCurrencies),
                'amount' => fake()->randomFloat(2, 10, 200000),
                'transaction_type' => fake()->randomElement($types),
                'status' => fake()->randomElement($statuses),
            ]);
        }
    }
}
