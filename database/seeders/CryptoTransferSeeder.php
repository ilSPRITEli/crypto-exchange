<?php

namespace Database\Seeders;

use App\Models\CryptoTransfer;
use App\Models\Cryptocurrency;
use App\Models\User;
use Illuminate\Database\Seeder;

class CryptoTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();
        $cryptos = Cryptocurrency::query()->get();

        if ($users->isEmpty() || $cryptos->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'completed', 'failed'];

        foreach (range(1, 10) as $i) {
            $sender = $users->random();
            $isInternal = fake()->boolean(60);

            $receiverId = null;
            $externalAddress = null;

            if ($isInternal) {
                $receiverId = $users->where('id', '!=', $sender->id)->random()->id;
            } else {
                $externalAddress = '0x' . fake()->regexify('[A-Fa-f0-9]{40}');
            }

            CryptoTransfer::query()->create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiverId,
                'cryptocurrency_id' => $cryptos->random()->id,
                'amount' => fake()->randomFloat(8, 0.0001, 1),
                'transfer_type' => $isInternal ? 'internal' : 'external',
                'external_address' => $externalAddress,
                'status' => fake()->randomElement($statuses),
            ]);
        }
    }
}
