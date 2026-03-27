<?php

namespace App\Http\Controllers;

use App\Models\CryptoTransfer;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function internalTransfer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sender_id' => ['required', 'uuid', 'exists:users,id'],
            'receiver_id' => ['required', 'uuid', 'exists:users,id', 'different:sender_id'],
            'cryptocurrency_id' => ['required', 'uuid', 'exists:cryptocurrencies,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $transfer = DB::transaction(function () use ($data): CryptoTransfer {
            /** @var Wallet|null $senderWallet */
            $senderWallet = Wallet::query()
                ->lockForUpdate()
                ->where('user_id', $data['sender_id'])
                ->where('cryptocurrency_id', $data['cryptocurrency_id'])
                ->first();

            if (! $senderWallet) {
                abort(422, 'sender_wallet_not_found');
            }

            if (! $senderWallet->hasSufficientBalance($data['amount'])) {
                abort(422, 'insufficient_balance');
            }

            /** @var Wallet $receiverWallet */
            $receiverWallet = Wallet::query()
                ->lockForUpdate()
                ->firstOrCreate(
                    [
                        'user_id' => $data['receiver_id'],
                        'cryptocurrency_id' => $data['cryptocurrency_id'],
                    ],
                    [
                        'balance' => '0',
                    ]
                );

            $senderWallet->withdraw($data['amount']);
            $receiverWallet->deposit($data['amount']);

            return CryptoTransfer::query()->create([
                ...$data,
                'transfer_type' => 'internal',
                'external_address' => null,
                'status' => 'completed',
            ]);
        });
        $transfer->load(['sender', 'receiver', 'cryptocurrency']);

        return response()->json([
            'message' => 'created',
            'data' => $transfer,
        ], 201);
    }

    public function externalTransfer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sender_id' => ['required', 'uuid', 'exists:users,id'],
            'cryptocurrency_id' => ['required', 'uuid', 'exists:cryptocurrencies,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'external_address' => ['required', 'string', 'max:255'],
        ]);

        $transfer = DB::transaction(function () use ($data): CryptoTransfer {
            /** @var Wallet|null $senderWallet */
            $senderWallet = Wallet::query()
                ->lockForUpdate()
                ->where('user_id', $data['sender_id'])
                ->where('cryptocurrency_id', $data['cryptocurrency_id'])
                ->first();

            if (! $senderWallet) {
                abort(422, 'sender_wallet_not_found');
            }

            if (! $senderWallet->hasSufficientBalance($data['amount'])) {
                abort(422, 'insufficient_balance');
            }

            $senderWallet->withdraw($data['amount']);

            return CryptoTransfer::query()->create([
                ...$data,
                'receiver_id' => null,
                'transfer_type' => 'external',
                'status' => 'pending',
            ]);
        });
        $transfer->load(['sender', 'receiver', 'cryptocurrency']);

        return response()->json([
            'message' => 'created',
            'data' => $transfer,
        ], 201);
    }

    public function history(Request $request): JsonResponse
    {
        $query = CryptoTransfer::query()
            ->with(['sender', 'receiver', 'cryptocurrency'])
            ->latest();

        if (! $request->filled('user_id')) {
            return response()->json(['message' => 'user_id_required'], 422);
        }

        $request->validate([
            'user_id' => ['uuid', 'exists:users,id'],
        ]);

        $userId = (string) $request->input('user_id');

        $query->where(function ($q) use ($userId): void {
            $q->where('sender_id', $userId)
                ->orWhere('receiver_id', $userId);
        });

        return response()->json($query->paginate(20));
    }
}
