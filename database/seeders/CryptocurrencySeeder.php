<?php

namespace Database\Seeders;

use App\Models\Cryptocurrency;
use Illuminate\Database\Seeder;

class CryptocurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['code' => 'BTC', 'name' => 'Bitcoin', 'network' => 'Bitcoin', 'is_active' => true],
            ['code' => 'ETH', 'name' => 'Ethereum', 'network' => 'Ethereum', 'is_active' => true],
            ['code' => 'XRP', 'name' => 'XRP', 'network' => 'XRP Ledger', 'is_active' => true],
        ];

        foreach ($rows as $row) {
            Cryptocurrency::query()->updateOrCreate(
                ['code' => $row['code']],
                $row
            );
        }
    }
}
