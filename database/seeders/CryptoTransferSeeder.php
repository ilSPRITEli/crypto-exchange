<?php

namespace Database\Seeders;

use App\Models\CryptoTransfer;
use App\Models\Cryptocurrency;
use App\Models\User;
use App\Models\Wallet;
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
            $crypto = $cryptos->random();
            $amount = (string) fake()->randomFloat(8, 0.0001, 1);

            if ($isInternal) {
                $receiverId = $users->where('id', '!=', $sender->id)->random()->id;
            } else {
                $externalAddress = '0x' . fake()->regexify('[A-Fa-f0-9]{40}');
            }

            $senderWallet = Wallet::query()
                ->where('user_id', $sender->id)
                ->where('cryptocurrency_id', $crypto->id)
                ->first();

            if (! $senderWallet) {
                continue;
            }

            if (! $senderWallet->hasSufficientBalance($amount)) {
                $senderWallet->deposit('5.00000000');
            }

            CryptoTransfer::query()->create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiverId,
                'cryptocurrency_id' => $crypto->id,
                'amount' => $amount,
                'transfer_type' => $isInternal ? 'internal' : 'external',
                'external_address' => $externalAddress,
                'status' => fake()->randomElement($statuses),
            ]);
        }
    }
}
