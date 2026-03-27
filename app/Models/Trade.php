<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[Fillable([
    'order_id',
    'buyer_id',
    'seller_id',
    'cryptocurrency_id',
    'fiat_currency',
    'price_per_unit',
    'amount',
    'total_price',
    'trade_status',
])]
class Trade extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'price_per_unit' => 'decimal:2',
            'amount' => 'decimal:8',
            'total_price' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function cryptocurrency(): BelongsTo
    {
        return $this->belongsTo(Cryptocurrency::class);
    }

    public function fiatTransactions(): HasMany
    {
        return $this->hasMany(FiatTransaction::class);
    }

    public function calculateTotal(): string
    {
        return bcmul((string) $this->price_per_unit, (string) $this->amount, 2);
    }
}
