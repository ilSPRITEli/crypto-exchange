<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[Fillable(['user_id', 'cryptocurrency_id', 'balance'])]
class Wallet extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:8',
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

    public function hasSufficientBalance(string|float|int $amount): bool
    {
        return bccomp((string) $this->balance, (string) $amount, 8) >= 0;
    }

    public function deposit(string|float|int $amount): self
    {
        $this->balance = bcadd((string) $this->balance, (string) $amount, 8);
        $this->save();

        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function withdraw(string|float|int $amount): self
    {
        if (! $this->hasSufficientBalance($amount)) {
            throw new \InvalidArgumentException('Insufficient balance.');
        }

        $this->balance = bcsub((string) $this->balance, (string) $amount, 8);
        $this->save();

        return $this;
    }
}
