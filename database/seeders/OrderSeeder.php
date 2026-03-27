<?php

namespace Database\Seeders;

use App\Models\Cryptocurrency;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
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

        $orderTypes = ['buy', 'sell'];
        $fiatCurrencies = ['THB', 'USD'];

        foreach (range(1, 12) as $i) {
            $crypto = $cryptos->random();
            $user = $users->random();
            $orderType = fake()->randomElement($orderTypes);
            $amount = (string) fake()->randomFloat(8, 0.01, 2);

            // For sell orders, ensure user has a wallet with enough balance.
            if ($orderType === 'sell') {
                $wallet = Wallet::query()
                    ->where('user_id', $user->id)
                    ->where('cryptocurrency_id', $crypto->id)
                    ->first();

                if ($wallet && ! $wallet->hasSufficientBalance($amount)) {
                    $wallet->deposit('50.00000000');
                }
            }

            Order::query()->create([
                'user_id' => $user->id,
                'cryptocurrency_id' => $crypto->id,
                'order_type' => $orderType,
                'fiat_currency' => fake()->randomElement($fiatCurrencies),
                'price_per_unit' => (string) fake()->randomFloat(2, 100, 200000),
                'amount' => $amount,
                'remaining_amount' => $amount,
                'status' => 'open',
            ]);
        }
    }
}
