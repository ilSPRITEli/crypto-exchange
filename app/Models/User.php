<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function buyTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'buyer_id');
    }

    public function sellTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'seller_id');
    }

    public function sentCryptoTransfers(): HasMany
    {
        return $this->hasMany(CryptoTransfer::class, 'sender_id');
    }

    public function receivedCryptoTransfers(): HasMany
    {
        return $this->hasMany(CryptoTransfer::class, 'receiver_id');
    }

    public function sentFiatTransactions(): HasMany
    {
        return $this->hasMany(FiatTransaction::class, 'sender_id');
    }

    public function receivedFiatTransactions(): HasMany
    {
        return $this->hasMany(FiatTransaction::class, 'receiver_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
