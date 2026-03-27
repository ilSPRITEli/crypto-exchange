<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Trade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'fiat_currency' => ['required', Rule::in(['THB', 'USD'])],
            'price_per_unit' => ['required', 'numeric', 'gt:0'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        try {
            $trade = DB::transaction(function () use ($data): Trade {
                $order = Order::query()->lockForUpdate()->findOrFail($data['order_id']);

                if (! $order->isOpen()) {
                    abort(422, 'order_not_open');
                }

                if ((string) $order->cryptocurrency_id !== (string) $data['cryptocurrency_id']) {
                    abort(422, 'cryptocurrency_mismatch');
                }

                if ((string) $order->fiat_currency !== (string) $data['fiat_currency']) {
                    abort(422, 'fiat_currency_mismatch');
                }

                if (bccomp((string) $order->price_per_unit, (string) $data['price_per_unit'], 2) !== 0) {
                    abort(422, 'price_mismatch');
                }

                if ($order->order_type === 'sell' && (string) $order->user_id !== (string) $data['seller_id']) {
                    abort(422, 'seller_must_be_order_owner');
                }
                if ($order->order_type === 'buy' && (string) $order->user_id !== (string) $data['buyer_id']) {
                    abort(422, 'buyer_must_be_order_owner');
                }

                if (bccomp((string) $data['amount'], (string) $order->remaining_amount, 8) === 1) {
                    abort(422, 'amount_exceeds_remaining');
                }

                $trade = Trade::query()->create([
                    ...$data,
                    'total_price' => Trade::calculateTotalAmount($data['price_per_unit'], $data['amount']),
                    'trade_status' => 'pending_payment',
                ]);

                $order->decreaseRemainingAmount($data['amount']);
                if (bccomp((string) $order->remaining_amount, '0', 8) === 0) {
                    $order->update(['status' => 'completed']);
                }

                return $trade;
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $trade->load(['order', 'buyer', 'seller', 'cryptocurrency']);

        return response()->json([
            'message' => 'created',
            'data' => $trade,
        ], 201);
    }

    public function complete(string $id): JsonResponse
    {
        $trade = Trade::query()->findOrFail($id);
        if ($trade->trade_status === 'completed') {
            return response()->json(['message' => 'trade_already_completed'], 422);
        }
        if ($trade->trade_status === 'cancelled') {
            return response()->json(['message' => 'trade_already_cancelled'], 422);
        }

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
        if ($trade->trade_status === 'completed') {
            return response()->json(['message' => 'trade_already_completed'], 422);
        }
        if ($trade->trade_status === 'cancelled') {
            return response()->json(['message' => 'trade_already_cancelled'], 422);
        }

        DB::transaction(function () use ($trade): void {
            $order = Order::query()->lockForUpdate()->findOrFail($trade->order_id);

            $newRemaining = bcadd((string) $order->remaining_amount, (string) $trade->amount, 8);
            if (bccomp($newRemaining, (string) $order->amount, 8) === 1) {
                $newRemaining = (string) $order->amount;
            }

            $order->update([
                'remaining_amount' => $newRemaining,
                'status' => 'open',
            ]);

            $trade->update(['trade_status' => 'cancelled']);
        });

        return response()->json([
            'message' => 'cancelled',
            'data' => $trade->fresh([
                'order', 'buyer', 'seller', 'cryptocurrency', 'fiatTransactions',
            ]),
        ]);
    }
}
