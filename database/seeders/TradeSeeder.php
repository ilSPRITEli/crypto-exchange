<?php

namespace Database\Seeders;

use App\Models\Cryptocurrency;
use App\Models\Order;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Database\Seeder;

class TradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();

        $orders = Order::query()
            ->with('cryptocurrency')
            ->get();

        if ($orders->isEmpty() || $users->count() < 2) {
            return;
        }

        $tradeStatuses = ['pending_payment', 'paid', 'completed', 'cancelled'];
        $fiatCurrencies = ['THB', 'USD'];

        foreach (range(1, 10) as $i) {
            $order = $orders->random();

            $buyer = $users->random();
            $seller = $users->where('id', '!=', $buyer->id)->random();

            $amount = fake()->randomFloat(8, 0.001, 1);
            $price = fake()->randomFloat(2, 100, 200000);

            Trade::query()->create([
                'order_id' => $order->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'cryptocurrency_id' => $order->cryptocurrency_id,
                'fiat_currency' => fake()->randomElement($fiatCurrencies),
                'price_per_unit' => $price,
                'amount' => $amount,
                'total_price' => $amount * $price,
                'trade_status' => fake()->randomElement($tradeStatuses),
            ]);
        }
    }
}
