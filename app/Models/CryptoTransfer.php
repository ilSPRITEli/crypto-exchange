<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[Fillable([
    'sender_id',
    'receiver_id',
    'cryptocurrency_id',
    'amount',
    'transfer_type',
    'external_address',
    'status',
])]
class CryptoTransfer extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function cryptocurrency(): BelongsTo
    {
        return $this->belongsTo(Cryptocurrency::class);
    }

    public function isInternal(): bool
    {
        return $this->transfer_type === 'internal';
    }

    public function isExternal(): bool
    {
        return $this->transfer_type === 'external';
    }
}
