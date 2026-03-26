<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[Fillable([
    'user_id',
    'cryptocurrency_id',
    'order_type',
    'fiat_currency',
    'price_per_unit',
    'amount',
    'remaining_amount',
    'status',
])]
class Order extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'price_per_unit' => 'decimal:2',
            'amount' => 'decimal:8',
            'remaining_amount' => 'decimal:8',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cryptocurrency(): BelongsTo
    {
        return $this->belongsTo(Cryptocurrency::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }
}
