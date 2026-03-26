<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CryptocurrencySeeder::class,
            UserSeeder::class,
            WalletSeeder::class,
            OrderSeeder::class,
            TradeSeeder::class,
            FiatTransactionSeeder::class,
            CryptoTransferSeeder::class,
        ]);
    }
}
