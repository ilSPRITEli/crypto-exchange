<?php

namespace Database\Seeders;

use App\Models\Cryptocurrency;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();
        $cryptos = Cryptocurrency::query()->get();

        $statuses = ['open', 'completed', 'cancelled'];
        $orderTypes = ['buy', 'sell'];
        $fiatCurrencies = ['THB', 'USD'];

        foreach (range(1, 12) as $i) {
            $amount = fake()->randomFloat(8, 0.01, 2);
            $remaining = fake()->randomFloat(8, 0, $amount);

            Order::query()->create([
                'user_id' => $users->random()->id,
                'cryptocurrency_id' => $cryptos->random()->id,
                'order_type' => fake()->randomElement($orderTypes),
                'fiat_currency' => fake()->randomElement($fiatCurrencies),
                'price_per_unit' => fake()->randomFloat(2, 100, 200000),
                'amount' => $amount,
                'remaining_amount' => $remaining,
                'status' => fake()->randomElement($statuses),
            ]);
        }
    }
}
