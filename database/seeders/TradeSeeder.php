<?php

namespace Database\Seeders;

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

        $orders = Order::query()->where('status', 'open')->get();

        if ($orders->isEmpty() || $users->count() < 2) {
            return;
        }

        foreach (range(1, 8) as $i) {
            /** @var Order $order */
            $order = $orders->random();

            $owner = $users->firstWhere('id', $order->user_id);
            if (! $owner) {
                continue;
            }

            $counterparty = $users->where('id', '!=', $owner->id)->random();

            $buyer = $order->order_type === 'buy' ? $owner : $counterparty;
            $seller = $order->order_type === 'sell' ? $owner : $counterparty;

            $remaining = (string) $order->remaining_amount;
            if (bccomp($remaining, '0', 8) <= 0) {
                continue;
            }

            // trade amount must be <= remaining
            $amount = (string) fake()->randomFloat(8, 0.001, (float) $remaining);
            if (bccomp($amount, $remaining, 8) === 1) {
                $amount = $remaining;
            }

            $trade = Trade::query()->create([
                'order_id' => $order->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'cryptocurrency_id' => $order->cryptocurrency_id,
                'fiat_currency' => $order->fiat_currency,
                'price_per_unit' => $order->price_per_unit,
                'amount' => $amount,
                'total_price' => Trade::calculateTotalAmount($order->price_per_unit, $amount),
                'trade_status' => 'pending_payment',
            ]);

            // reflect the same rule as controller: decrease remaining and complete if 0
            $order->decreaseRemainingAmount($amount);
            if (bccomp((string) $order->remaining_amount, '0', 8) === 0) {
                $order->update(['status' => 'completed']);
            }
        }
    }
}
