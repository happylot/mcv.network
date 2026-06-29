<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['account_id', 'provider', 'provider_reference', 'amount_cents', 'currency', 'status', 'metadata', 'confirmed_at'])]
class Payment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'confirmed_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function formattedAmount(): string
    {
        return sprintf('$%s', number_format($this->amount_cents / 100, 2));
    }
}
