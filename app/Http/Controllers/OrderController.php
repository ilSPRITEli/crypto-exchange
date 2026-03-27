<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    public function show(string $id): JsonResponse
    {
        $order = Order::query()->findOrFail($id);

        return response()->json([
            'data' => $order,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'cryptocurrency_id' => ['required', 'uuid', 'exists:cryptocurrencies,id'],
            'order_type' => ['required', 'string'],
            'fiat_currency' => ['required', 'string'],
            'price_per_unit' => ['required', 'numeric'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $amount = (float) $data['amount'];

        $order = Order::query()->create([
            ...$data,
            'remaining_amount' => $amount,
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'created',
            'data' => $order,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $order = Order::query()->findOrFail($id);

        $data = $request->validate([
            'order_type' => ['sometimes', 'string'],
            'fiat_currency' => ['sometimes', 'string'],
            'price_per_unit' => ['sometimes', 'numeric'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'remaining_amount' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string'],
        ]);

        $order->fill($data)->save();

        return response()->json([
            'message' => 'updated',
            'data' => $order->fresh(),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $order = Order::query()->findOrFail($id);
        $order->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'cancelled',
            'data' => $order->fresh(),
        ]);
    }
}
