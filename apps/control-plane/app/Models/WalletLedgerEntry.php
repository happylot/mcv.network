<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'wallet_id',
    'type',
    'direction',
    'amount_cents',
    'currency',
    'status',
    'reference_type',
    'reference_id',
    'idempotency_key',
    'metadata',
])]
class WalletLedgerEntry extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function formattedAmount(): string
    {
        return sprintf('$%s', number_format($this->amount_cents / 100, 2));
    }
}
