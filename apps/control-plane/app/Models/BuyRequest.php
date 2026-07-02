<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'account_id',
    'title',
    'category',
    'budget_cents',
    'description',
    'status',
])]
class BuyRequest extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'budget_cents' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function formattedBudget(): string
    {
        return sprintf('$%s', number_format($this->budget_cents / 100, 2));
    }
}
