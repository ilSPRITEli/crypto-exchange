<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $trades = Trade::query()
            ->with(['order', 'buyer', 'seller', 'cryptocurrency'])
            ->latest()
            ->paginate(20);

        return response()->json($trades);
    }

    public function show(string $id): JsonResponse
    {
        $trade = Trade::query()
            ->with(['order', 'buyer', 'seller', 'cryptocurrency', 'fiatTransactions'])
            ->findOrFail($id);

        return response()->json([
            'data' => $trade,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'uuid', 'exists:orders,id'],
            'buyer_id' => ['required', 'uuid', 'exists:users,id'],
            'seller_id' => ['required', 'uuid', 'exists:users,id', 'different:buyer_id'],
            'cryptocurrency_id' => ['required', 'uuid', 'exists:cryptocurrencies,id'],
            'fiat_currency' => ['required', 'string'],
            'price_per_unit' => ['required', 'numeric'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $trade = Trade::query()->create([
            ...$data,
            'total_price' => Trade::calculateTotalAmount($data['price_per_unit'], $data['amount']),
            'trade_status' => 'pending_payment',
        ]);
        $trade->load(['order', 'buyer', 'seller', 'cryptocurrency']);

        return response()->json([
            'message' => 'created',
            'data' => $trade,
        ], 201);
    }

    public function complete(string $id): JsonResponse
    {
        $trade = Trade::query()->findOrFail($id);
        $trade->update(['trade_status' => 'completed']);

        return response()->json([
            'message' => 'completed',
            'data' => $trade->fresh([
                'order', 'buyer', 'seller', 'cryptocurrency', 'fiatTransactions',
            ]),
        ]);
    }

    public function cancel(string $id): JsonResponse
    {
        $trade = Trade::query()->findOrFail($id);
        $trade->update(['trade_status' => 'cancelled']);

        return response()->json([
            'message' => 'cancelled',
            'data' => $trade->fresh([
                'order', 'buyer', 'seller', 'cryptocurrency', 'fiatTransactions',
            ]),
        ]);
    }
}
