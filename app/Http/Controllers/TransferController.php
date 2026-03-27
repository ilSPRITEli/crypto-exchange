<?php

namespace App\Http\Controllers;

use App\Models\CryptoTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function internalTransfer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sender_id' => ['required', 'uuid', 'exists:users,id'],
            'receiver_id' => ['required', 'uuid', 'exists:users,id', 'different:sender_id'],
            'cryptocurrency_id' => ['required', 'uuid', 'exists:cryptocurrencies,id'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $transfer = CryptoTransfer::query()->create([
            ...$data,
            'transfer_type' => 'internal',
            'external_address' => null,
            'status' => 'completed',
        ]);

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
            'amount' => ['required', 'numeric', 'min:0'],
            'external_address' => ['required', 'string', 'max:255'],
        ]);

        $transfer = CryptoTransfer::query()->create([
            ...$data,
            'receiver_id' => null,
            'transfer_type' => 'external',
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'created',
            'data' => $transfer,
        ], 201);
    }

    public function history(Request $request): JsonResponse
    {
        $query = CryptoTransfer::query()->latest();

        if ($request->filled('user_id')) {
            $request->validate([
                'user_id' => ['uuid', 'exists:users,id'],
            ]);

            $userId = (string) $request->input('user_id');
            $query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
        }

        return response()->json($query->paginate(20));
    }
}
