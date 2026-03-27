<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $wallets = Wallet::query()
            ->with(['user', 'cryptocurrency'])
            ->latest()
            ->paginate(20);

        return response()->json($wallets);
    }

    public function showByUser(string $userId): JsonResponse
    {
        $wallets = Wallet::query()
            ->with('cryptocurrency')
            ->where('user_id', $userId)
            ->orderBy('cryptocurrency_id')
            ->get();

        return response()->json([
            'data' => $wallets,
        ]);
    }
}
