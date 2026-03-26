<?php

namespace Database\Seeders;

use App\Models\Cryptocurrency;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();
        $cryptos = Cryptocurrency::query()->get();

        foreach ($users as $user) {
            foreach ($cryptos as $crypto) {
                Wallet::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'cryptocurrency_id' => $crypto->id,
                    ],
                    [
                        'balance' => fake()->randomFloat(8, 0, 10),
                    ]
                );
            }
        }
    }
}
