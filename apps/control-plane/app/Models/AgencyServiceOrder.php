<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_account_id',
    'agency_account_id',
    'agency_service_id',
    'amount_cents',
    'currency',
    'status',
    'brief',
    'reference_url',
    'delivery_url',
    'agency_notes',
    'submitted_at',
    'approved_at',
    'approved_by_account_id',
    'due_at',
])]
class AgencyServiceOrder extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'due_at' => 'datetime',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function clientAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'client_account_id');
    }

    public function agencyAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'agency_account_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(AgencyService::class, 'agency_service_id');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function formattedAmount(): string
    {
        return sprintf('$%s', number_format($this->amount_cents / 100, 2));
    }
}
