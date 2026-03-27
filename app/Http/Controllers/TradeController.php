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
            ->latest()
            ->paginate(20);

        return response()->json($trades);
    }

    public function show(string $id): JsonResponse
    {
        $trade = Trade::query()->findOrFail($id);

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

        $amount = (float) $data['amount'];
        $price = (float) $data['price_per_unit'];

        $trade = Trade::query()->create([
            ...$data,
            'total_price' => $amount * $price,
            'trade_status' => 'pending_payment',
        ]);

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
            'data' => $trade->fresh(),
        ]);
    }

    public function cancel(string $id): JsonResponse
    {
        $trade = Trade::query()->findOrFail($id);
        $trade->update(['trade_status' => 'cancelled']);

        return response()->json([
            'message' => 'cancelled',
            'data' => $trade->fresh(),
        ]);
    }
}
