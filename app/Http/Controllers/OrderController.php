<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Models\Order;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['user', 'cryptocurrency'])
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    public function show(string $id): JsonResponse
    {
        $order = Order::query()
            ->with(['user', 'cryptocurrency', 'trades'])
            ->findOrFail($id);

        return response()->json([
            'data' => $order,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'cryptocurrency_id' => ['required', 'uuid', 'exists:cryptocurrencies,id'],
            'order_type' => ['required', Rule::in(['buy', 'sell'])],
            'fiat_currency' => ['required', Rule::in(['THB', 'USD'])],
            'price_per_unit' => ['required', 'numeric', 'gt:0'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $amount = (float) $data['amount'];

        $crypto = Cryptocurrency::query()->findOrFail($data['cryptocurrency_id']);
        if (! $crypto->is_active) {
            return response()->json(['message' => 'cryptocurrency_inactive'], 422);
        }

        if ($data['order_type'] === 'sell') {
            $wallet = Wallet::query()
                ->where('user_id', $data['user_id'])
                ->where('cryptocurrency_id', $data['cryptocurrency_id'])
                ->first();

            if (! $wallet) {
                return response()->json(['message' => 'wallet_not_found'], 422);
            }

            if (! $wallet->hasSufficientBalance($data['amount'])) {
                return response()->json(['message' => 'insufficient_balance'], 422);
            }
        }

        $order = Order::query()->create([
            ...$data,
            'remaining_amount' => $amount,
            'status' => 'open',
        ]);
        $order->load(['user', 'cryptocurrency']);

        return response()->json([
            'message' => 'created',
            'data' => $order,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $order = Order::query()->findOrFail($id);
        if (! $order->isOpen()) {
            return response()->json(['message' => 'order_not_open'], 422);
        }

        $data = $request->validate([
            'order_type' => ['sometimes', Rule::in(['buy', 'sell'])],
            'fiat_currency' => ['sometimes', Rule::in(['THB', 'USD'])],
            'price_per_unit' => ['sometimes', 'numeric', 'gt:0'],
            'amount' => ['sometimes', 'numeric', 'gt:0'],
        ]);

        if (array_key_exists('amount', $data)) {
            $used = bcsub((string) $order->amount, (string) $order->remaining_amount, 8);
            if (bccomp((string) $data['amount'], $used, 8) < 0) {
                return response()->json(['message' => 'amount_less_than_used'], 422);
            }

            $order->remaining_amount = bcsub((string) $data['amount'], $used, 8);
        }

        if (array_key_exists('order_type', $data) && $data['order_type'] === 'sell') {
            $wallet = Wallet::query()
                ->where('user_id', $order->user_id)
                ->where('cryptocurrency_id', $order->cryptocurrency_id)
                ->first();

            if (! $wallet) {
                return response()->json(['message' => 'wallet_not_found'], 422);
            }

            if (! $wallet->hasSufficientBalance($order->remaining_amount)) {
                return response()->json(['message' => 'insufficient_balance'], 422);
            }
        }

        $order->fill($data)->save();

        return response()->json([
            'message' => 'updated',
            'data' => $order->fresh(['user', 'cryptocurrency', 'trades']),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $order = Order::query()->findOrFail($id);
        if (! $order->isOpen()) {
            return response()->json(['message' => 'order_not_open'], 422);
        }
        $order->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'cancelled',
            'data' => $order->fresh(['user', 'cryptocurrency', 'trades']),
        ]);
    }
}
